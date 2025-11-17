# MQTT Bridge - Hidroganik Monitoring

Service untuk menyimpan data sensor dari MQTT ke MySQL secara otomatis.

## Cara Menjalankan

### Opsi 1: Manual (Terminal Terlihat)

1. Buka terminal/PowerShell di folder `mqtt-bridge`
2. Jalankan:
   ```bash
   npm start
   ```
3. Biarkan terminal tetap terbuka

### Opsi 2: Otomatis (Background)

1. **Double-click** file `start-bridge.bat`
2. Terminal akan muncul dan menampilkan log
3. Minimize window (jangan close)

### Opsi 3: Background Invisible (Recommended)

1. **Double-click** file `start-bridge-background.vbs`
2. Service berjalan tanpa window
3. Data tetap tersimpan otomatis

## Menghentikan Service

### Jika menggunakan .bat:

- Tekan `Ctrl+C` di window terminal

### Jika menggunakan .vbs (background):

1. Buka **Task Manager** (Ctrl+Shift+Esc)
2. Tab **Details**
3. Cari **node.exe**
4. Klik kanan → **End Task**

## Auto-Start saat Windows Boot

### Cara 1: Startup Folder

1. Tekan `Win+R`
2. Ketik: `shell:startup`
3. Copy file `start-bridge-background.vbs` ke folder yang terbuka
4. Service akan auto-start setiap Windows boot

### Cara 2: Task Scheduler (Lebih Reliable)

1. Buka **Task Scheduler**
2. **Create Basic Task**
3. Name: `Hidroganik MQTT Bridge`
4. Trigger: **When the computer starts**
5. Action: **Start a program**
6. Program: `C:\laragon\www\HidroganikAlfa\mqtt-bridge\start-bridge-background.vbs`
7. Finish

## Verifikasi Service Berjalan

### Cek di Terminal:

```bash
# Cek process Node.js
tasklist | findstr node.exe
```

### Cek di Browser:

1. Buka web monitoring: http://localhost:8080
2. Halaman Dashboard atau Log Data
3. Kirim data dari ESP32
4. Data muncul dalam 1-2 detik

## Troubleshooting

### Service tidak jalan?

- Pastikan Node.js sudah terinstall
- Cek `npm install` sudah berhasil
- Pastikan PHP server (Laragon) running di port 8080

### Data tidak masuk database?

- Cek ESP32 publish ke topic yang benar: `hidroganik/kebun-a/telemetry`
- Cek MQTT broker: `wss://broker.emqx.io:8084/mqtt`
- Cek log di terminal MQTT Bridge
- Cek API endpoint: http://localhost:8080/api/telemetry/latest

### Service crash terus?

- Cek log error di terminal
- Pastikan tidak ada duplikat process
- Restart Laragon (PHP + MySQL)

## Environment Variables (Optional)

Buat file `.env` di folder ini untuk custom settings:

```env
MQTT_URL=wss://broker.emqx.io:8084/mqtt
MQTT_TOPIC=hidroganik/+/telemetry
INGEST_URL=http://localhost:8080/api/telemetry/ingest
INGEST_TOKEN=rahasia-hidroganik-2025
```

## Logs

Service akan print log ke console:

- `[ingest] Connecting to MQTT`
- `[ingest] MQTT connected`
- `[ingest] Stored: kebun-a ts=2025-11-17 13:30:00`

## Status

✅ Service running = Data tersimpan otomatis
❌ Service stopped = Data tidak tersimpan (tapi tetap di MQTT broker)

---

**PENTING:**
Web monitoring TIDAK perlu dibuka untuk menyimpan data.
Hanya MQTT Bridge yang harus running!
