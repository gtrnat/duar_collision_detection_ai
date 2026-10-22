#include <Arduino.h>
#include <Wire.h>
#include <Adafruit_MPU6050.h>
#include <Adafruit_Sensor.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <TinyGPS++.h>

// ============================================================
// TFLite Micro
// ============================================================
#include "tensorflow/lite/micro/micro_mutable_op_resolver.h"
#include "tensorflow/lite/micro/micro_interpreter.h"
#include "tensorflow/lite/schema/schema_generated.h"
#include "esp_heap_caps.h"

// Memasukkan file array C dari model CNN 1D kamu
#include "cnn1d_finetuned_model.h"

// ============================================================
// KONFIGURASI WIFI & SERVER LARAVEL
// ============================================================
const char* ssid     = "Fadhil eka";
const char* password = "lupalupa";
const char* serverUrl = "http://172.20.10.6:8000/api/inference";
// ============================================================
// KONFIGURASI GPS (NEO-6M)
// ============================================================
TinyGPSPlus gps;
HardwareSerial gpsSerial(2); // Menggunakan UART2 ESP32 (RX2=16, TX2=17)

Adafruit_MPU6050 mpu;

// ============================================================
// KONFIGURASI MODEL & BUFFER
// ============================================================
constexpr int SEQUENCE_LENGTH = 400;
constexpr int NUM_FEATURES = 6;
constexpr int POST_IMPACT_SAMPLES = 200;

constexpr size_t TENSOR_ARENA_SIZE = 256 * 1024; // 256KB alokasi di PSRAM

uint8_t* tensor_arena = nullptr;
const tflite::Model* model = nullptr;
tflite::MicroInterpreter* interpreter = nullptr;
TfLiteTensor* input_tensor  = nullptr;
TfLiteTensor* output_tensor = nullptr;

float sensorBuffer[SEQUENCE_LENGTH][NUM_FEATURES];
int bufferHead = 0;
bool bufferFilled = false;

enum SystemState { STATE_MONITORING, STATE_POST_IMPACT, STATE_INFERENCE };
SystemState currentState = STATE_MONITORING;
int postImpactCount = 0;

// NILAI NORMALISASI (Berdasarkan dataset training)
const float mean[6] = { -0.04837704689842171f, 0.0368204283316364f, 0.9504260838200755f, -0.19198466047095838f, 0.1383929210545511f, 0.13852383004667057f };
const float stdv[6] = { 0.2881367806215683f, 0.28212807702356923f, 0.2738498481688984f, 79.98958475861723f, 81.1581088004506f, 80.33908322606602f };

constexpr float CRASH_THRESHOLD = 0.50f;
constexpr float IMPACT_G_THRESHOLD = 2.0f; 

void pushSample(float ax, float ay, float az, float gx, float gy, float gz) {
    sensorBuffer[bufferHead][0] = ax;
    sensorBuffer[bufferHead][1] = ay;
    sensorBuffer[bufferHead][2] = az;
    sensorBuffer[bufferHead][3] = gx;
    sensorBuffer[bufferHead][4] = gy;
    sensorBuffer[bufferHead][5] = gz;
    bufferHead = (bufferHead + 1) % SEQUENCE_LENGTH;
    if (bufferHead == 0) bufferFilled = true;
}

// ============================================================
// SETUP FUNGSI
// ============================================================
bool setupModel() {
    Serial.println("\n===== SETUP MODEL TFLITE =====");
    model = tflite::GetModel(cnn1d_finetuned_model);
    if (!model || model->version() != TFLITE_SCHEMA_VERSION) return false;

    tensor_arena = (uint8_t*)heap_caps_malloc(TENSOR_ARENA_SIZE, MALLOC_CAP_SPIRAM | MALLOC_CAP_8BIT);
    if (!tensor_arena) return false;

    static tflite::MicroMutableOpResolver<15> resolver;
    resolver.AddConv2D();         
    resolver.AddMaxPool2D();      
    resolver.AddMean();           
    resolver.AddFullyConnected(); 
    resolver.AddRelu();           
    resolver.AddLogistic();       
    resolver.AddReshape();        
    resolver.AddExpandDims();     

    static tflite::MicroInterpreter static_interpreter(model, resolver, tensor_arena, TENSOR_ARENA_SIZE);
    interpreter = &static_interpreter;
    if (interpreter->AllocateTensors() != kTfLiteOk) return false;

    input_tensor  = interpreter->input(0);
    output_tensor = interpreter->output(0);
    return true;
}

bool setupMPU() {
    Serial.println("\n===== SETUP MPU6050 =====");
    if (!mpu.begin()) return false;
    mpu.setAccelerometerRange(MPU6050_RANGE_8_G);
    mpu.setGyroRange(MPU6050_RANGE_500_DEG);
    mpu.setFilterBandwidth(MPU6050_BAND_21_HZ);
    return true;
}

void setupWiFi() {
    Serial.println("\n===== SETUP WIFI =====");
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
    Serial.println("\nWiFi Connected!");
}

// ============================================================
// KIRIM DATA KE LARAVEL
// ============================================================
void sendDataToLaravel(float ai_score) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("Error: WiFi tidak terhubung.");
        return;
    }

    // Ekstrak max g_force
    float max_g_force = 0;
    for (int i = 0; i < SEQUENCE_LENGTH; i++) {
        float ax = sensorBuffer[i][0];
        float ay = sensorBuffer[i][1];
        float az = sensorBuffer[i][2];
        float g = sqrt(ax*ax + ay*ay + az*az);
        if (g > max_g_force) max_g_force = g;
    }

    // Ekstrak Pitch (Dongak/Nungging) & Roll (Kanan/Kiri) dari kondisi akhir statis
    float max_pitch = 0;
    float max_roll  = 0;

    // Asumsi: Sumbu X menghadap Kanan bodi mobil, Sumbu Y menghadap Depan bodi mobil.
    for (int i = SEQUENCE_LENGTH - 50; i < SEQUENCE_LENGTH; i++) {
        float ax = sensorBuffer[i][0];
        float ay = sensorBuffer[i][1];
        float az = sensorBuffer[i][2];

        float roll  = atan2(ax, az) * 180.0 / PI; // Kanan-Kiri
        float pitch = atan2(ay, az) * 180.0 / PI; // Dongak-Nungging

        if (abs(roll) > abs(max_roll))   max_roll = roll;
        if (abs(pitch) > abs(max_pitch)) max_pitch = pitch;
    }

    Serial.printf("%f %f", max_roll, max_pitch);

    double latitude = gps.location.isValid() ? gps.location.lat() : -6.2030;
    double longitude = gps.location.isValid() ? gps.location.lng() : 106.8400;
    float speed = gps.speed.isValid() ? gps.speed.kmph() : 0.0;

    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");

    // Mengirim payload terstruktur yang sesuai dengan CrashController
    String payload = "{";
    payload += "\"unit_id\":\"B-01\",";
    payload += "\"name\":\"Ahmad Fauzi\",";
    payload += "\"vehicle\":\"RC Crash Detector Prototype\",";
    payload += "\"latitude\":" + String(latitude, 6) + ",";
    payload += "\"longitude\":" + String(longitude, 6) + ",";
    payload += "\"speed\":" + String(speed, 1) + ",";
    payload += "\"max_g_force\":" + String(max_g_force, 2) + ",";
    payload += "\"pitch\":" + String(max_pitch, 2) + ",";
    payload += "\"roll\":" + String(max_roll, 2) + ",";
    payload += "\"ai_confidence\":" + String(ai_score, 4);
    payload += "}";

    Serial.println("\nMengirim Payload:");
    Serial.println(payload);

    int httpResponseCode = http.POST(payload);
    if (httpResponseCode > 0) {
        Serial.printf("Berhasil! HTTP Code: %d\n", httpResponseCode);
    } else {
        Serial.printf("Error HTTP: %d\n", httpResponseCode);
    }
    http.end();
}

void runInference() {
    Serial.println("\n[INFO] Menjalankan inferensi...");
    for (int i = 0; i < SEQUENCE_LENGTH; i++) {
        int idx = (bufferHead + i) % SEQUENCE_LENGTH;
        for (int j = 0; j < NUM_FEATURES; j++) {
            float normalized = (sensorBuffer[idx][j] - mean[j]) / stdv[j];
            input_tensor->data.f[i * NUM_FEATURES + j] = normalized;
        }
    }

    interpreter->Invoke();
    float crashProb = output_tensor->data.f[0];
    
    Serial.printf("Skor AI Tabrakan : %.4f\n", crashProb);
    
    if (crashProb >= CRASH_THRESHOLD) {
        Serial.println("STATUS: 🚨 KECELAKAAN TERKONFIRMASI! MENGIRIM DATA...");
        sendDataToLaravel(crashProb);
    } else {
        Serial.println("STATUS: ✅ AMAN (Guncangan Biasa)");
    }

    delay(3000); // Cooldown mencegah multi-trigger
}

// ============================================================
// MAIN ROUTINE
// ============================================================
void setup() {
    Serial.begin(115200);
    gpsSerial.begin(9600, SERIAL_8N1, 16, 17);
    
    delay(2000);
    Wire.begin();
    setupWiFi();
    
    if (!setupModel() || !setupMPU()) {
        Serial.println("Gagal Inisialisasi Sensor/Model!");
        while (1) delay(100);
    }

    Serial.println("\nSistem Siaga. Menunggu Guncangan/Impact...");
}

void loop() {
    // Polling GPS
    while (gpsSerial.available() > 0) {
        gps.encode(gpsSerial.read());
    }

    // Polling Sensor MPU
    sensors_event_t a, g, temp;
    mpu.getEvent(&a, &g, &temp);

    float ax = a.acceleration.x / 9.81f;
    float ay = a.acceleration.y / 9.81f;
    float az = a.acceleration.z / 9.81f;
    float gx = g.gyro.x * RAD_TO_DEG;
    float gy = g.gyro.y * RAD_TO_DEG;
    float gz = g.gyro.z * RAD_TO_DEG;
    float totalG = sqrtf(ax*ax + ay*ay + az*az);

    pushSample(ax, ay, az, gx, gy, gz);

    // State Machine
    switch (currentState) {
        case STATE_MONITORING:
            if (bufferFilled && totalG >= IMPACT_G_THRESHOLD) {
                Serial.printf("\n⚡ IMPACT! (%.2fG). Merekam buffer akhir...\n", totalG);
                postImpactCount = 0;
                currentState = STATE_POST_IMPACT;
            }
            break;
        case STATE_POST_IMPACT:
            postImpactCount++;
            if (postImpactCount >= POST_IMPACT_SAMPLES) currentState = STATE_INFERENCE;
            break;
        case STATE_INFERENCE:
            runInference();
            bufferFilled = false; // Reset buffer menghindari sisa guncangan
            currentState = STATE_MONITORING;
            Serial.println("\nSistem Siaga Kembali...");
            break;
    }
    delay(10);
}