<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>CrashGuard — User Companion</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root {
      --bg: #0b0b0b;
      --surface: #141414;
      --surface-2: #1a1a1a;
      --surface-3: #202020;
      --border: #2a2a2a;
      --text: #f4f4f2;
      --text-dim: #a3a29e;
      --text-faint: #6b6a66;
      --white: #ffffff;
      --green: #4ADE80;
      --green-dim: #244a34;
      --red: #FF5B54;
      --red-dim: #4a201d;
      --font-display: 'Space Grotesk', sans-serif;
      --font-mono: 'IBM Plex Mono', monospace;
      --radius-md: 16px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
    body {
      background: #000;
      color: var(--text);
      font-family: var(--font-mono);
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 20px 10px;
    }

    .phone-user {
      position: relative;
      width: 100%;
      max-width: 392px;
      height: 840px;
      max-height: 92vh;
      background: var(--bg);
      border-radius: 36px;
      border: 1px solid var(--border);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
    }

    .user-header {
      padding: 20px 20px 12px;
      border-bottom: 1px solid var(--border);
      text-align: center;
      background: var(--surface);
      flex-shrink: 0;
    }
    .user-header h1 { font-family: var(--font-display); font-size: 15px; color: #fff; }
    .user-header p { font-size: 9px; color: var(--text-dim); margin-top: 3px; }

    .user-scroll {
      flex: 1;
      padding: 14px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      overflow-y: auto;
      scrollbar-width: none;
    }
    .user-scroll::-webkit-scrollbar { display: none; }

    .status-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 16px;
      text-align: center;
      transition: all 0.3s ease;
    }
    .status-icon {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background: rgba(74, 222, 128, 0.1);
      color: var(--green);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin: 0 auto 10px;
    }
    .status-card.danger { border-color: var(--red); background: rgba(255, 91, 84, 0.08); }
    .status-card.danger .status-icon { background: rgba(255, 91, 84, 0.2); color: var(--red); }
    .status-title { font-family: var(--font-display); font-weight: 700; font-size: 13.5px; margin-bottom: 4px; }
    .status-desc { font-size: 10px; color: var(--text-dim); line-height: 1.4; }

    .enable-sound-btn {
      width: 100%;
      background: var(--green-dim);
      color: var(--green);
      border: 1px solid var(--green);
      padding: 10px;
      border-radius: var(--radius-md);
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 11px;
      cursor: pointer;
      margin-bottom: 4px;
    }

    .map-wrap {
      position: relative;
      height: 190px;
      border-radius: var(--radius-md);
      overflow: hidden;
      border: 1px solid var(--border);
      flex-shrink: 0;
    }
    #map { width: 100%; height: 100%; background: #141414; }
    .leaflet-tile { filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3); }

    .map-chip {
      position: absolute; top: 10px; left: 10px; z-index: 1000;
      background: rgba(10,10,10,.85); border: 1px solid var(--border);
      border-radius: 999px; padding: 4px 10px; font-size: 9px;
      color: var(--text-dim); display: flex; align-items: center; gap: 5px;
    }
    .map-chip .live-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--green); }
    .map-chip .live-dot.danger { background: var(--red); }

    .telemetry-section {
      display: none;
      flex-direction: column;
      gap: 12px;
      animation: fadeIn 0.5s ease forwards;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .user-profile-bar {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 14px; background: var(--surface-2);
      border: 1px solid var(--border); border-radius: var(--radius-md);
    }
    .user-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: var(--surface-3); display: flex;
      align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
    }
    .user-id .name { font-family: var(--font-display); font-weight: 700; font-size: 13px; }
    .user-id .role { font-size: 9.5px; color: var(--text-faint); }

    .stat-trio { display: grid; grid-template-columns: repeat(3,1fr); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; background: var(--surface); }
    .stat-trio .st { padding: 10px 6px; text-align: center; border-right: 1px solid var(--border); }
    .stat-trio .st:last-child { border-right: none; }
    .stat-trio .st .lbl { font-size: 8px; color: var(--text-faint); }
    .stat-trio .st .val { font-family: var(--font-display); font-size: 16px; font-weight: 700; margin-top: 4px; }

    .detail-panel { border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px; background: var(--surface-2); }
    .dp-head { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .dp-head h3 { font-size: 10.5px; font-weight: 700; color: var(--white); }
    .dp-head .tag { font-size: 8px; font-weight: 700; padding: 2px 6px; border-radius: 4px; }

    .tilt-widget { display: flex; align-items: center; gap: 14px; }
    .tilt-stage { position: relative; width: 80px; height: 60px; border-radius: 8px; background: var(--surface-3); border: 1px solid var(--border); overflow: hidden; }
    .tilt-horizon { position: absolute; left: -20%; right: -20%; top: 50%; height: 1px; background: repeating-linear-gradient(90deg, var(--text-faint) 0 6px, transparent 6px 11px); }
    .tilt-car { position: absolute; left: 50%; top: 50%; width: 38px; height: 18px; transform: translate(-50%,-50%) rotate(0deg); transition: transform .35s ease; }
    .tilt-readout .deg { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--green); }
    .tilt-readout .dir { font-size: 9.5px; color: var(--text-dim); }

    .incoming-call {
      position: absolute; inset: 0; background: rgba(10, 10, 10, 0.96);
      backdrop-filter: blur(15px); z-index: 10000; display: flex;
      flex-direction: column; justify-content: space-between;
      padding: 70px 24px 80px; opacity: 0; pointer-events: none; transition: opacity 0.4s ease;
    }
    .incoming-call.active { opacity: 1; pointer-events: auto; }

    .call-avatar {
      width: 90px; height: 90px; border-radius: 50%; background: var(--surface-2);
      border: 2px solid var(--border); display: flex; align-items: center;
      justify-content: center; font-size: 38px; margin: 0 auto 14px;
      box-shadow: 0 0 25px rgba(255,91,84,0.25);
    }
    .call-title { font-family: var(--font-display); font-size: 18px; font-weight: 700; color: #fff; text-align: center; }
    .call-sub { font-size: 10.5px; color: var(--text-dim); text-align: center; margin-top: 4px; }
    .call-status { font-size: 11.5px; color: var(--green); font-weight: 600; text-align: center; margin-top: 14px; letter-spacing: 0.1em; }

    .call-actions { display: flex; justify-content: center; gap: 40px; }
    .btn-action {
      width: 60px; height: 60px; border-radius: 50%; border: none;
      color: white; display: flex; align-items: center; justify-content: center;
      cursor: pointer; font-size: 22px; box-shadow: 0 10px 20px rgba(0,0,0,0.5);
    }
    .btn-decline { background: var(--red); }
    .btn-accept { background: var(--green); }
  </style>
</head>
<body>

<div class="phone-user">
  <div class="user-header">
    <h1>🛡️ CRASHGUARD USER</h1>
    <p>Safety Companion Device · Live Monitoring</p>
  </div>

  <div class="user-scroll">

    <button class="enable-sound-btn" id="btnEnableSound" onclick="enableAudioPermission()">
      🔊 KLIK DISINI UNTUK MENGAKTIFKAN RINGTONE & TELEPON
    </button>

    <div class="status-card" id="userStatusCard">
      <div class="status-icon" id="userStatusIcon">🟢</div>
      <div class="status-title" id="userStatusTitle">Sistem Safe-Ride Aktif</div>
      <div class="status-desc" id="userStatusDesc">Kendaraan terhubung secara real-time dengan Pusat Kendali Response Console.</div>
    </div>

    <div class="map-wrap">
      <div id="map"></div>
      <div class="map-chip">
        <span class="live-dot" id="mapLiveDot"></span>
        <span id="mapChipText">LOKASI REAL-TIME</span>
      </div>
    </div>

    <div class="telemetry-section" id="telemetrySection">

      <div class="user-profile-bar">
        <div class="user-avatar" id="uAvatar">AF</div>
        <div class="user-id">
          <div class="name" id="uName">Ahmad Fauzi</div>
          <div class="role" id="uVehicle">RC Crash Detector Prototype · B-01</div>
        </div>
      </div>

      <div class="stat-trio">
        <div class="st"><div class="lbl">Kecepatan Max</div><div class="val" id="uSpeed">0<span>km/j</span></div></div>
        <div class="st"><div class="lbl">Pitch Max</div><div class="val" id="uPitch">0.0<span>°</span></div></div>
        <div class="st"><div class="lbl">G‑Force Max</div><div class="val" id="uG">0.0<span>g</span></div></div>
      </div>

      <div class="detail-panel">
        <div class="dp-head">
          <h3>Indikator Kemiringan (Pitch)</h3>
          <span class="tag" id="uTiltTag">NORMAL</span>
        </div>
        <div class="tilt-widget">
          <div class="tilt-stage">
            <div class="tilt-horizon" id="uTiltHorizon"></div>
            <div class="tilt-car" id="uTiltCar">
              <svg viewBox="0 0 44 22" fill="none">
                <rect x="4" y="6" width="36" height="10" rx="4" id="uCarBody" fill="#4ADE80"/>
                <rect x="12" y="1" width="18" height="8" rx="3" id="uCarRoof" fill="#4ADE80"/>
                <circle cx="12" cy="17" r="4" fill="#0b0b0b" stroke="#4ADE80" stroke-width="1.6"/>
                <circle cx="32" cy="17" r="4" fill="#0b0b0b" stroke="#4ADE80" stroke-width="1.6"/>
              </svg>
            </div>
          </div>
          <div class="tilt-readout">
            <div class="deg" id="uTiltDegText">0.0°</div>
            <div class="dir" id="uTiltDirText">Stabil / Rata</div>
          </div>
        </div>
      </div>

      <div class="detail-panel">
        <div class="dp-head">
          <h3>Accelerometer Axis (G-Force X/Y/Z)</h3>
        </div>
        <div style="height: 140px; position: relative;">
          <canvas id="userAccelChart"></canvas>
        </div>
      </div>

      <div class="detail-panel">
        <div class="dp-head">
          <h3>Gyroscope Rate (°/s)</h3>
        </div>
        <div style="height: 140px; position: relative;">
          <canvas id="userGyroChart"></canvas>
        </div>
      </div>

      <div class="detail-panel" style="border-left: 3px solid var(--green);">
        <div class="dp-head">
          <h3 style="color: var(--green);">🏥 Rumah Sakit Rujukan Evakuasi</h3>
          <span class="tag" style="background: var(--green-dim); color: var(--green);">RUJUKAN AKTIF</span>
        </div>
        <div style="font-family: var(--font-display); font-size: 14px; font-weight: 700; color: var(--white); margin-top: 6px;" id="uHospitalName">
          Menunggu Konfirmasi...
        </div>
        <div style="font-size: 9.5px; color: var(--text-dim); margin-top: 4px; line-height: 1.4;">
          Korban akan dirujuk ke faskes di atas. Harap keluarga/tim medis segera menuju ke lokasi untuk penanganan lanjutan.
        </div>
      </div>

    </div>

  </div>

  <div class="incoming-call" id="userCallOverlay">
    <div>
      <div class="call-avatar">🚑</div>
      <div class="call-title">DISPATCH DISHUB BALAI</div>
      <div class="call-sub">Panggilan Peringatan Darurat Otomatis</div>
      <div class="call-status" id="userCallState">PANGGILAN MASUK...</div>
    </div>

    <div class="call-actions">
      <button class="btn-action btn-decline" id="btnUserDecline">✖</button>
      <button class="btn-action btn-accept" id="btnUserAccept">📞</button>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let accidentMarkers = {};
let activeCrashId = null;
let isRingtonePlaying = false;
let ringtoneInterval = null;
let audioCtx = null;
let userAccelChart = null, userGyroChart = null;

function enableAudioPermission() {
  if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  if (audioCtx.state === 'suspended') audioCtx.resume();
  document.getElementById('btnEnableSound').style.display = 'none';
}

function initUserMap() {
  map = L.map('map', { zoomControl: false, attributionControl: false }).setView([-6.2030, 106.8400], 14);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

  const greenIcon = L.divIcon({
    className: 'custom-div-icon',
    html: `<div style="background-color:#4ADE80;width:10px;height:10px;border-radius:50%;border:2px solid white;box-shadow:0 0 8px #4ADE80;"></div>`,
    iconSize: [10, 10], iconAnchor: [5, 5]
  });

  L.marker([-6.2030, 106.8400], { icon: greenIcon }).addTo(map);
}

function startRingtone() {
  if (isRingtonePlaying) return;
  isRingtonePlaying = true;
  if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  if (audioCtx.state === 'suspended') audioCtx.resume();

  ringtoneInterval = setInterval(() => {
    if (!isRingtonePlaying) return;
    let osc = audioCtx.createOscillator();
    let gain = audioCtx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(800, audioCtx.currentTime);
    gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
    osc.connect(gain); gain.connect(audioCtx.destination);
    osc.start(); osc.stop(audioCtx.currentTime + 0.4);
  }, 500);
}

function stopRingtone() {
  if (ringtoneInterval) clearInterval(ringtoneInterval);
  isRingtonePlaying = false;
}

function renderUserCharts(data) {
  let raw = {};
  try {
    raw = typeof data.raw_payload === 'string' ? JSON.parse(data.raw_payload) : (data.raw_payload || {});
  } catch(e) {}

  const gForce = parseFloat(data.g_force || raw.max_g_force || 0);
  const pitchVal = parseFloat(data.pitch || 0);

  const gx = raw.gyro_x_graph || [0.1, 0.2, 0.3, gForce * 0.9, 0.2, 0.1];
  const gy = raw.gyro_y_graph || [0.0, 0.1, 0.2, gForce * 0.6, 0.3, 0.1];
  const gz = raw.gyro_z_graph || [1.0, 1.0, 1.1, gForce * 1.2, 1.1, 1.0];
  const speed = raw.speed_graph || [0, 5, 12, pitchVal * 4, pitchVal * 2, 5];

  const labels = gx.map((_, i) => `t${i}`);

  const ctxA = document.getElementById('userAccelChart').getContext('2d');
  if (userAccelChart) userAccelChart.destroy();
  userAccelChart = new Chart(ctxA, {
    type: 'line',
    data: {
      labels: labels.slice(0, 20),
      datasets: [
        { label: 'Gyro X (°/s)', data: gx.slice(0, 20), borderColor: '#FF5B54', borderWidth: 1.8, tension: 0.3 },
        { label: 'Gyro Y (°/s)', data: gy.slice(0, 20), borderColor: '#3B82F6', borderWidth: 1.8, tension: 0.3 },
        { label: 'Gyro Z (°/s)', data: gz.slice(0, 20), borderColor: '#4ADE80', borderWidth: 1.8, tension: 0.3 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#a3a29e', font: { size: 8 } } } },
      scales: { x: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 7.5 } } }, y: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 7.5 } } } }
    }
  });

  const ctxG = document.getElementById('userGyroChart').getContext('2d');
  if (userGyroChart) userGyroChart.destroy();
  userGyroChart = new Chart(ctxG, {
    type: 'line',
    data: {
      labels: labels.slice(0, 20),
      datasets: [
        { label: 'Kecepatan (km/h)', data: speed.slice(0, 20), borderColor: '#EAB308', borderWidth: 1.8, tension: 0.3 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#a3a29e', font: { size: 8 } } } },
      scales: { x: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 7.5 } } }, y: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 7.5 } } } }
    }
  });
}

function showFullTelemetryData(data) {
  if (!data) return;
  let raw = {};
  try {
    raw = typeof data.raw_payload === 'string' ? JSON.parse(data.raw_payload) : (data.raw_payload || {});
  } catch(e) {}

  // AMBIL LANGSUNG DARI KOLOM PITCH DATABASE
  const pitchVal = parseFloat(data.pitch || 0);
  const speedVal = parseFloat(data.speed || raw.max_speed || 0);
  const gVal = parseFloat(data.g_force || raw.max_g_force || 0);

  document.getElementById('uName').textContent = raw.name || data.name || 'Ahmad Fauzi';
  document.getElementById('uVehicle').textContent = `${raw.vehicle || data.vehicle || 'RC Crash Detector Prototype'} · ${raw.unitId || data.unit_id || 'B-01'}`;

  document.getElementById('uSpeed').innerHTML = `${speedVal.toFixed(1)}<span>km/j</span>`;
  document.getElementById('uPitch').innerHTML = `${Math.abs(pitchVal).toFixed(1)}<span>°</span>`;
  document.getElementById('uG').innerHTML = `${gVal.toFixed(1)}<span>g</span>`;

  // UI ELEMEN ROTASI MOBIL USER
  const uTiltCar = document.getElementById('uTiltCar');
  const uTiltHorizon = document.getElementById('uTiltHorizon');
  const uTiltDegText = document.getElementById('uTiltDegText');
  const uTiltDirText = document.getElementById('uTiltDirText');
  const uTiltTag = document.getElementById('uTiltTag');
  const uTiltValDisplay = document.getElementById('uPitch');

  if (uTiltCar) uTiltCar.style.transform = `translate(-50%,-50%) rotate(${-pitchVal}deg)`;
  if (uTiltHorizon) uTiltHorizon.style.transform = `rotate(${-pitchVal * 0.15}deg)`;
  if (uTiltDegText) uTiltDegText.textContent = `${pitchVal.toFixed(1)}°`;

  if (Math.abs(pitchVal) > 30) {
    if (uTiltDirText) uTiltDirText.textContent = pitchVal > 0 ? 'Dongak Ekstrem (Depan Naik)' : 'Nungging Ekstrem (Depan Turun)';
    if (uTiltDegText) uTiltDegText.style.color = '#FF5B54';
    if (uTiltValDisplay) uTiltValDisplay.style.color = '#FF5B54';
    if (uTiltTag) {
      uTiltTag.textContent = 'OVER LIMIT';
      uTiltTag.style.background = 'var(--red-dim)';
      uTiltTag.style.color = '#FF5B54';
    }
    document.getElementById('uCarBody')?.setAttribute('fill', '#FF5B54');
    document.getElementById('uCarRoof')?.setAttribute('fill', '#FF5B54');
  } else {
    if (uTiltDirText) uTiltDirText.textContent = Math.abs(pitchVal) < 3 ? 'Stabil / Rata' : 'Kemiringan Normal';
    if (uTiltDegText) uTiltDegText.style.color = '#4ADE80';
    if (uTiltValDisplay) uTiltValDisplay.style.color = '#4ADE80';
    if (uTiltTag) {
      uTiltTag.textContent = 'NORMAL';
      uTiltTag.style.background = 'var(--green-dim)';
      uTiltTag.style.color = '#4ADE80';
    }
    document.getElementById('uCarBody')?.setAttribute('fill', '#4ADE80');
    document.getElementById('uCarRoof')?.setAttribute('fill', '#4ADE80');
  }

  const realHospitals = [
    "Rumah Sakit Tarakan Jakarta",
    "Rumah Sakit Cipto Mangunkusumo",
    "Rumah Sakit Fatmawati",
    "Rumah Sakit Pelni Jakarta",
    "Rumah Sakit Islam Jakarta"
  ];
  const hospitalName = data.assigned_hospital || raw.assigned_hospital || realHospitals[0];
  document.getElementById('uHospitalName').textContent = hospitalName;

  renderUserCharts(data);
  document.getElementById('telemetrySection').style.display = 'flex';
}

function listenForUserAlerts() {
  fetch('/api/pending-accidents')
    .then(res => res.json())
    .then(res => {
      const card = document.getElementById('userStatusCard');
      const icon = document.getElementById('userStatusIcon');
      const title = document.getElementById('userStatusTitle');
      const desc = document.getElementById('userStatusDesc');
      const dot = document.getElementById('mapLiveDot');
      const chip = document.getElementById('mapChipText');

      if (res.count > 0) {
        const accidents = res.data || [];
        const item = accidents[0];

        card.classList.add('danger');
        icon.textContent = '🚨';
        title.textContent = 'KECELAKAAN TERDETEKSI!';
        desc.innerHTML = `Peringatan Darurat Aktif atas nama <b>${item.name}</b>.`;

        dot.classList.add('danger');
        chip.textContent = 'INSIDEN TERDETEKSI';

        clearAccidentMarkers();
        const accLat = parseFloat(item.lat);
        const accLng = parseFloat(item.lng);
        if (!isNaN(accLat) && !isNaN(accLng)) {
          const redIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color:#FF5B54;width:16px;height:16px;border-radius:50%;border:2px solid white;box-shadow:0 0 10px #FF5B54;"></div>`,
            iconSize: [16, 16], iconAnchor: [8, 8]
          });
          L.marker([accLat, accLng], { icon: redIcon }).addTo(map);
          map.flyTo([accLat, accLng], 15);
        }

        if (activeCrashId !== item.id) {
          activeCrashId = item.id;
          triggerUserIncomingCall(item);
        }

      } else {
        fetch('/api/accidents')
          .then(r => r.json())
          .then(allData => {
            if (allData && allData.length > 0) {
              const latest = allData[0];

              card.classList.remove('danger');
              icon.textContent = '🟢';
              title.textContent = 'Sistem Safe-Ride Aktif';
              desc.innerHTML = `Terhubung dengan unit <b>${latest.name}</b> (${latest.vehicle}).`;

              dot.classList.remove('danger');
              chip.textContent = 'LOKASI REAL-TIME';

              clearAccidentMarkers();
              const accLat = parseFloat(latest.lat);
              const accLng = parseFloat(latest.lng);
              if (!isNaN(accLat) && !isNaN(accLng)) {
                const redIcon = L.divIcon({
                  className: 'custom-div-icon',
                  html: `<div style="background-color:#FF5B54;width:16px;height:16px;border-radius:50%;border:2px solid white;box-shadow:0 0 10px #FF5B54;"></div>`,
                  iconSize: [16, 16], iconAnchor: [8, 8]
                });
                L.marker([accLat, accLng], { icon: redIcon }).addTo(map);
              }

              showFullTelemetryData(latest);
            } else {
              card.classList.remove('danger');
              icon.textContent = '🟢';
              title.textContent = 'Sistem Safe-Ride Aktif';
              desc.textContent = 'Kendaraan terhubung secara real-time dengan Pusat Kendali Response Console.';
              dot.classList.remove('danger');
              chip.textContent = 'LOKASI REAL-TIME';
              document.getElementById('telemetrySection').style.display = 'none';
            }
          });
      }
    })
    .catch(err => console.log("Waiting for user alert stream...", err));
}

function clearAccidentMarkers() {
  Object.keys(accidentMarkers).forEach(k => map.removeLayer(accidentMarkers[k]));
  accidentMarkers = {};
}

function triggerUserIncomingCall(accidentData) {
  const overlay = document.getElementById('userCallOverlay');
  const state = document.getElementById('userCallState');

  overlay.classList.add('active');
  state.textContent = "PANGGILAN MASUK...";
  startRingtone();

  const realHospitals = [
    "Rumah Sakit Tarakan Jakarta",
    "Rumah Sakit Cipto Mangunkusumo",
    "Rumah Sakit Fatmawati",
    "Rumah Sakit Pelni Jakarta",
    "Rumah Sakit Islam Jakarta"
  ];

  const hospitalName = accidentData.assigned_hospital || realHospitals[Math.floor(Math.random() * realHospitals.length)];
  accidentData.assigned_hospital = hospitalName;

  document.getElementById('btnUserDecline').onclick = () => {
    stopRingtone();
    overlay.classList.remove('active');
    window.speechSynthesis.cancel();
    showFullTelemetryData(accidentData);
  };

  document.getElementById('btnUserAccept').onclick = () => {
    stopRingtone();
    state.textContent = "TERHUBUNG (00:01)";

    const speechText = `Peringatan Darurat. Telah terdeteksi kecelakaan bernama ${accidentData.name || 'Ahmad Fauzi'}. Korban saat ini dirujuk menuju ${hospitalName}. Harap segera menuju ke rumah sakit rujukan.`;

    window.speechSynthesis.cancel();
    let utterance = new SpeechSynthesisUtterance(speechText);
    utterance.lang = 'id-ID';
    window.speechSynthesis.speak(utterance);

    utterance.onend = function() {
      overlay.classList.remove('active');
      showFullTelemetryData(accidentData);
    };

    setTimeout(() => {
      overlay.classList.remove('active');
      showFullTelemetryData(accidentData);
    }, 8000);
  };
}

document.addEventListener("DOMContentLoaded", function() {
  initUserMap();
  listenForUserAlerts();
  setInterval(listenForUserAlerts, 2000);
});
</script>

</body>
</html>
