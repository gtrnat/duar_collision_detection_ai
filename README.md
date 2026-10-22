# DUAR — Detection of Unexpected Accident Risks

DUAR (Detection of Unexpected Accident Risks) is an accident-risk detection project designed for units equipped with an ESP32, an MPU6050 motion sensor, and a NEO-6M GPS module. The system is intended to detect potential accident events using motion data and associate detected events with location information, with a Laravel web application for supporting software functionality and a MySQL database.

## Project Components

- **ESP32** — embedded controller that interfaces with the connected modules.
- **MPU6050** — measures acceleration and angular velocity to provide motion data for accident-risk detection.
- **NEO-6M GPS** — provides geographic location data for supported tracking and event-location features.
- **Laravel** — web application framework.
- **MySQL** — relational database.
- **Arduino IDE** — development environment for the embedded firmware.

## Repository Structure

```text
DUAR/
├── ArduinoIDE/     # Arduino / ESP32 firmware project
├── app/            # Laravel application logic
├── bootstrap/      # Laravel bootstrapping
├── config/         # Application configuration
├── database/       # Database migrations, seeders, and factories
├── public/         # Public entry point and assets
├── resources/      # Views and frontend source files
├── routes/         # Application routes
├── storage/        # Logs, cache, and generated files
├── tests/          # Automated tests
├── .env.example    # Example environment configuration
├── artisan         # Laravel command-line interface
├── composer.json   # PHP dependencies and scripts
├── package.json    # JavaScript dependencies and scripts
└── vite.config.js  # Vite configuration
```

## Prerequisites

Install the following tools before setting up the project:

- Git
- PHP (use a version compatible with the project's `composer.json`)
- Composer
- Node.js and npm
- MySQL
- Arduino IDE (for working with the ESP32 firmware)

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/gtrnat/DUAR.git
cd DUAR
```

### 2. Install dependencies

Install the PHP dependencies:

```bash
composer install
```

Install the frontend dependencies:

```bash
npm install
```

### 3. Configure environment variables

Create a local environment file.

**Windows Command Prompt:**

```cmd
copy .env.example .env
```

**macOS / Linux / Git Bash:**

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

### 4. Configure MySQL

Create a MySQL database for DUAR, then update the database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=duar
DB_USERNAME=root
DB_PASSWORD=
```

Replace the database name, username, and password with values for your local MySQL setup. Do not commit real credentials to the repository.

Run the database migrations:

```bash
php artisan migrate
```

### 5. Build frontend assets

For local development, run the Vite development server:

```bash
npm run dev
```

Alternatively, build the frontend assets:

```bash
npm run build
```

### 6. Start the Laravel application

In a separate terminal, run:

```bash
php artisan serve
```

Open the local URL shown in the terminal, usually:

```text
http://127.0.0.1:8000
```

Keep `npm run dev` running while developing if the application relies on Vite's development server.

## ESP32 and Sensor Setup

The embedded component is located in `ArduinoIDE/`.

1. Open the relevant Arduino sketch in Arduino IDE.
2. Install the required ESP32 board support package and any libraries referenced by the firmware.
3. Connect the MPU6050 and NEO-6M modules according to the wiring defined by the project.
4. Select the correct ESP32 board and serial port.
5. Compile and upload the firmware.
6. Verify sensor readings and GPS reception in the Serial Monitor.

**Hardware note:** Pin assignments, communication settings, required libraries, and firmware-specific configuration should follow the source code in `ArduinoIDE/`. GPS reception may be limited indoors or where the antenna has a poor view of the sky.

## Testing

Run the Laravel test suite with:

```bash
php artisan test
```

## Security

- Keep `.env` out of version control.
- Never commit database passwords, API keys, or other secrets.
- Use secure credentials and appropriate access controls in production.
- Treat accident detection as a supporting feature; do not rely on it as the sole means of emergency detection or response.

## Contributing

1. Create a branch for your changes.
2. Make and test your changes.
3. Submit a pull request with a clear summary of the changes.

## License

No license is specified here. Check the repository for a `LICENSE` file or add an appropriate license before redistributing the project.
