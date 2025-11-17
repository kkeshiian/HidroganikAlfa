<?= $this->extend('layouts/main') ?>

<?= $this->section('head_extra') ?>
<style>
.calibration-card { margin-bottom: 1.5rem; }
.countdown { font-size: 3rem; font-weight: bold; }
.pulse-animation { animation: pulse 1s infinite; }
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
@media (max-width: 768px) {
    .countdown { font-size: 2rem; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-6">
    <h2 class="text-2xl font-bold text-success mb-2">Kalibrasi Sensor</h2>
    <p class="text-sm text-base-content/70">Atur kalibrasi untuk meningkatkan akurasi pembacaan sensor</p>
</div>

<div class="alert alert-info mb-6 shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <span class="text-sm">Kalibrasi akan diterapkan pada data baru yang masuk ke sistem</span>
</div>

<!-- Realtime Sensor Data -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <!-- Kebun A -->
    <div class="card bg-base-100 shadow-lg border border-primary/20">
        <div class="card-body p-4">
            <h3 class="font-bold text-primary mb-3">Data Sensor Kebun A</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">Suhu Air</div>
                    <div class="stat-value text-lg text-info" id="sensor-suhu-a">-- °C</div>
                    <div class="stat-desc text-xs text-base-content/50">Terkalibrasi</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">Suhu Mentah</div>
                    <div class="stat-value text-lg text-error" id="sensor-suhu-raw-a">-- °C</div>
                    <div class="stat-desc text-xs text-base-content/50">Dari Sensor</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">pH</div>
                    <div class="stat-value text-lg text-success" id="sensor-ph-a">--</div>
                    <div class="stat-desc text-xs text-base-content/50">Terkalibrasi</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">TDS</div>
                    <div class="stat-value text-lg text-warning" id="sensor-tds-a">-- ppm</div>
                    <div class="stat-desc text-xs text-base-content/50">Terkalibrasi</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">TDS Mentah</div>
                    <div class="stat-value text-lg text-error" id="sensor-tds-raw-a">-- ppm</div>
                    <div class="stat-desc text-xs text-base-content/50">Dari Sensor</div>
                </div>
            </div>
            <div class="text-xs text-base-content/50 mt-2" id="sensor-time-a">Menunggu data...</div>
        </div>
    </div>

    <!-- Kebun B -->
    <div class="card bg-base-100 shadow-lg border border-secondary/20">
        <div class="card-body p-4">
            <h3 class="font-bold text-secondary mb-3">Data Sensor Kebun B</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">Suhu Air</div>
                    <div class="stat-value text-lg text-info" id="sensor-suhu-b">-- °C</div>
                    <div class="stat-desc text-xs text-base-content/50">Terkalibrasi</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">Suhu Mentah</div>
                    <div class="stat-value text-lg text-error" id="sensor-suhu-raw-b">-- °C</div>
                    <div class="stat-desc text-xs text-base-content/50">Dari Sensor</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">pH</div>
                    <div class="stat-value text-lg text-success" id="sensor-ph-b">--</div>
                    <div class="stat-desc text-xs text-base-content/50">Terkalibrasi</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">TDS</div>
                    <div class="stat-value text-lg text-warning" id="sensor-tds-b">-- ppm</div>
                    <div class="stat-desc text-xs text-base-content/50">Terkalibrasi</div>
                </div>
                <div class="stat bg-base-200 rounded-lg p-3">
                    <div class="stat-title text-xs">TDS Mentah</div>
                    <div class="stat-value text-lg text-error" id="sensor-tds-raw-b">-- ppm</div>
                    <div class="stat-desc text-xs text-base-content/50">Dari Sensor</div>
                </div>
            </div>
            <div class="text-xs text-base-content/50 mt-2" id="sensor-time-b">Menunggu data...</div>
        </div>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="text-center my-8">
    <span class="loading loading-spinner loading-lg text-primary"></span>
    <p class="mt-4 text-sm">Memuat pengaturan kalibrasi...</p>
</div>

<!-- Calibration Forms -->
<div id="calibrationForms" class="hidden space-y-6"></div>

<!-- Help Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
    <div class="card bg-base-100 shadow border border-success/20">
        <div class="card-body p-4">
            <h3 class="font-semibold text-sm mb-2">Kalibrasi TDS</h3>
            <ol class="text-xs space-y-1 text-base-content/80">
                <li>1. Ukur dengan TDS meter standar</li>
                <li>2. Bandingkan dengan sensor</li>
                <li>3. Hitung nilai pengali (multiplier)</li>
            </ol>
            <div class="mt-3 p-2 bg-base-200 rounded text-xs">
                <strong>Contoh:</strong> Sensor: 850 ppm, Standar: 800 ppm<br>
                Multiplier = 800 ÷ 850 = 0.941
            </div>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow border border-info/20">
        <div class="card-body p-4">
            <h3 class="font-semibold text-sm mb-2">Kalibrasi Suhu</h3>
            <ol class="text-xs space-y-1 text-base-content/80">
                <li>1. Gunakan termometer standar</li>
                <li>2. Bandingkan pembacaan</li>
                <li>3. Hitung selisihnya (koreksi)</li>
            </ol>
            <div class="mt-3 p-2 bg-base-200 rounded text-xs">
                <strong>Contoh:</strong> Sensor: 26.5°C, Standar: 25.0°C<br>
                Koreksi = 25.0 - 26.5 = -1.5°C
            </div>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow border border-warning/20">
        <div class="card-body p-4">
            <h3 class="font-semibold text-sm mb-2">Kalibrasi pH</h3>
            <ol class="text-xs space-y-1 text-base-content/80">
                <li>1. Siapkan larutan buffer 4.01 & 6.86</li>
                <li>2. Celupkan sensor 30 detik per buffer</li>
                <li>3. Sistem otomatis hitung slope & intercept</li>
            </ol>
            <div class="mt-3 p-2 bg-base-200 rounded text-xs">
                <strong>Tips:</strong> Bilas sensor dengan air suling sebelum pindah buffer
            </div>
        </div>
    </div>
</div>

<script>
let calibrationData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadCalibrationSettings();
    loadLatestSensorData();
    // Auto refresh sensor data setiap 5 detik
    setInterval(loadLatestSensorData, 5000);
});

async function loadLatestSensorData() {
    try {
        const response = await fetch('<?= base_url('api/telemetry/latest') ?>');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            // Update Kebun A
            const kebunA = result.data.find(d => d.kebun === 'kebun-a');
            if (kebunA) {
                document.getElementById('sensor-suhu-a').textContent = kebunA.suhu_air ? kebunA.suhu_air + ' °C' : '-- °C';
                document.getElementById('sensor-suhu-raw-a').textContent = kebunA.suhu_mentah ? kebunA.suhu_mentah + ' °C' : '-- °C';
                document.getElementById('sensor-ph-a').textContent = kebunA.ph || '--';
                document.getElementById('sensor-tds-a').textContent = kebunA.tds ? kebunA.tds + ' ppm' : '-- ppm';
                document.getElementById('sensor-tds-raw-a').textContent = kebunA.tds_mentah ? kebunA.tds_mentah + ' ppm' : '-- ppm';
                document.getElementById('sensor-time-a').textContent = `Update: ${kebunA.date} ${kebunA.time}`;
            }
            
            // Update Kebun B
            const kebunB = result.data.find(d => d.kebun === 'kebun-b');
            if (kebunB) {
                document.getElementById('sensor-suhu-b').textContent = kebunB.suhu_air ? kebunB.suhu_air + ' °C' : '-- °C';
                document.getElementById('sensor-suhu-raw-b').textContent = kebunB.suhu_mentah ? kebunB.suhu_mentah + ' °C' : '-- °C';
                document.getElementById('sensor-ph-b').textContent = kebunB.ph || '--';
                document.getElementById('sensor-tds-b').textContent = kebunB.tds ? kebunB.tds + ' ppm' : '-- ppm';
                document.getElementById('sensor-tds-raw-b').textContent = kebunB.tds_mentah ? kebunB.tds_mentah + ' ppm' : '-- ppm';
                document.getElementById('sensor-time-b').textContent = `Update: ${kebunB.date} ${kebunB.time}`;
            }
        }
    } catch (error) {
        console.error('Error loading sensor data:', error);
    }
}

async function loadCalibrationSettings() {
    try {
        const response = await fetch('<?= base_url('api/calibration/settings') ?>');
        const result = await response.json();
        
        if (result.status === 'success') {
            calibrationData = result.data;
            renderCalibrationForms(result.data);
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('calibrationForms').classList.remove('hidden');
        } else {
            showError('Gagal memuat data kalibrasi');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error: ' + error.message);
    }
}

function renderCalibrationForms(data) {
    const container = document.getElementById('calibrationForms');
    container.innerHTML = '';
    
    data.forEach(setting => {
        const hasPhCalibration = setting.ph_slope !== null && setting.ph_intercept !== null;
        
        const formHtml = `
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body p-4 md:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg md:text-xl font-bold text-success">
                            ${setting.kebun.toUpperCase()} - Device ${setting.device}
                        </h3>
                        <div class="badge ${setting.active == 1 ? 'badge-success text-white' : 'badge-ghost'}">${setting.active == 1 ? 'Aktif' : 'Nonaktif'}</div>
                    </div>
                    
                    <form id="form-${setting.id}" onsubmit="saveCalibration(event, ${setting.id})">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            
                            <!-- pH Calibration -->
                            <div class="card bg-base-200 border border-base-300">
                                <div class="card-body p-4">
                                    <h4 class="font-semibold text-base mb-3 flex items-center gap-2">
                                        <span class="badge badge-sm badge-primary text-white">pH</span>
                                        Kalibrasi pH (2 Titik)
                                    </h4>
                                    
                                    ${hasPhCalibration ? `
                                        <div class="alert alert-success py-2 mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div class="text-xs">
                                                <div>Terkalibrasi</div>
                                                <div class="opacity-70">Slope: ${parseFloat(setting.ph_slope).toFixed(4)}</div>
                                            </div>
                                        </div>
                                    ` : `
                                        <div class="alert alert-warning py-2 mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            <span class="text-xs">Belum dikalibrasi</span>
                                        </div>
                                    `}
                                    
                                    <button type="button" class="btn btn-primary btn-sm btn-block mb-2" 
                                            onclick="startPhCalibration(${setting.id})">
                                        Mulai Kalibrasi
                                    </button>
                                    
                                    ${hasPhCalibration ? `
                                        <button type="button" class="btn btn-error btn-outline btn-xs btn-block" 
                                                onclick="clearPhCalibration(${setting.id})">
                                            Hapus Kalibrasi
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                            
                            <!-- TDS Calibration -->
                            <div class="card bg-base-200 border border-base-300">
                                <div class="card-body p-4">
                                    <h4 class="font-semibold text-base mb-3 flex items-center gap-2">
                                        <span class="badge badge-sm badge-success text-white">TDS</span>
                                        Kalibrasi TDS
                                    </h4>
                                    
                                    <div class="form-control mb-3">
                                        <label class="label py-1">
                                            <span class="label-text text-xs">Nilai Pengali</span>
                                        </label>
                                        <input type="number" class="input input-bordered input-sm" 
                                               name="tds_multiplier" 
                                               value="${setting.tds_multiplier}"
                                               step="0.001"
                                               min="0.1" max="2"
                                               onchange="updateTestCalc(${setting.id})">
                                    </div>
                                    
                                    <div class="text-xs text-base-content/70 mb-3 p-2 bg-base-100 rounded">
                                        Formula: TDS × Multiplier
                                    </div>
                                    
                                    <div class="divider my-2 text-xs">Test</div>
                                    <input type="number" class="input input-bordered input-sm w-full mb-1" 
                                           id="test-tds-${setting.id}" 
                                           placeholder="TDS Raw"
                                           onchange="updateTestCalc(${setting.id})">
                                    <div id="result-tds-${setting.id}" class="text-success font-semibold text-xs"></div>
                                </div>
                            </div>
                            
                            <!-- Suhu Calibration -->
                            <div class="card bg-base-200 border border-base-300">
                                <div class="card-body p-4">
                                    <h4 class="font-semibold text-base mb-3 flex items-center gap-2">
                                        <span class="badge badge-sm badge-info text-white">Suhu</span>
                                        Kalibrasi Suhu
                                    </h4>
                                    
                                    <div class="form-control mb-3">
                                        <label class="label py-1">
                                            <span class="label-text text-xs">Koreksi (°C)</span>
                                        </label>
                                        <input type="number" class="input input-bordered input-sm" 
                                               name="suhu_offset" 
                                               value="${setting.suhu_offset}"
                                               step="0.1"
                                               placeholder="-2 atau +1"
                                               onchange="updateTestCalc(${setting.id})">
                                    </div>
                                    
                                    <div class="text-xs text-base-content/70 mb-3 p-2 bg-base-100 rounded">
                                        Formula: Suhu + Koreksi
                                    </div>
                                    
                                    <div class="divider my-2 text-xs">Test</div>
                                    <input type="number" class="input input-bordered input-sm w-full mb-1" 
                                           id="test-suhu-${setting.id}" 
                                           placeholder="Suhu Raw"
                                           step="0.1"
                                           onchange="updateTestCalc(${setting.id})">
                                    <div id="result-suhu-${setting.id}" class="text-info font-semibold text-xs"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="divider my-3"></div>
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="form-control">
                                <label class="label cursor-pointer gap-2 p-0">
                                    <input type="checkbox" class="toggle toggle-success toggle-sm" 
                                           name="active" 
                                           ${setting.active == 1 ? 'checked' : ''}>
                                    <span class="label-text text-sm">Aktifkan Kalibrasi</span>
                                </label>
                            </div>
                            
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button type="button" class="btn btn-ghost btn-sm flex-1 sm:flex-none" onclick="loadCalibrationSettings()">
                                    Reset
                                </button>
                                <button type="submit" class="btn btn-success btn-sm flex-1 sm:flex-none">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;
        container.innerHTML += formHtml;
    });
}

function updateTestCalc(id) {
    const form = document.getElementById(`form-${id}`);
    const tdsRaw = parseFloat(document.getElementById(`test-tds-${id}`).value);
    const suhuRaw = parseFloat(document.getElementById(`test-suhu-${id}`).value);
    
    const tdsMultiplier = parseFloat(form.tds_multiplier.value) || 1;
    const suhuOffset = parseFloat(form.suhu_offset.value) || 0;
    
    if (!isNaN(tdsRaw)) {
        const tdsCalibrated = Math.round(tdsRaw * tdsMultiplier);
        document.getElementById(`result-tds-${id}`).textContent = 
            `→ Terkalibrasi: ${tdsCalibrated} ppm`;
    }
    
    if (!isNaN(suhuRaw)) {
        const suhuCalibrated = (suhuRaw + suhuOffset).toFixed(2);
        document.getElementById(`result-suhu-${id}`).textContent = 
            `→ Terkalibrasi: ${suhuCalibrated}°C`;
    }
}

async function saveCalibration(event, id) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const setting = calibrationData.find(s => s.id === id);
    
    const data = {
        id: id,
        ph_slope: setting?.ph_slope || null,
        ph_intercept: setting?.ph_intercept || null,
        tds_offset: 0,
        tds_multiplier: parseFloat(formData.get('tds_multiplier')) || 1,
        suhu_offset: parseFloat(formData.get('suhu_offset')) || 0,
        suhu_multiplier: 1,
        active: formData.get('active') ? 1 : 0
    };
    
    try {
        const response = await fetch('<?= base_url('api/calibration/update') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Kalibrasi berhasil disimpan!');
            loadCalibrationSettings();
        } else {
            showError(result.message || 'Gagal menyimpan');
        }
    } catch (error) {
        showError('Error: ' + error.message);
    }
}

function startPhCalibration(id) {
    const modal = document.createElement('div');
    modal.className = 'modal modal-open';
    modal.innerHTML = `
        <div class="modal-box max-w-lg">
            <h3 class="font-bold text-lg mb-4">Kalibrasi pH 2 Titik</h3>
            
            <div id="ph-cal-step-1">
                <div class="alert alert-info mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm">Celupkan sensor ke <strong>buffer pH 4.01</strong> dan tunggu 30 detik</span>
                </div>
                <div class="text-center mb-4">
                    <div class="countdown text-primary" id="countdown-1">30</div>
                    <div class="text-sm opacity-70">detik</div>
                </div>
                <button class="btn btn-primary btn-block btn-sm" onclick="startCountdown(1, ${id})">
                    Mulai Countdown
                </button>
            </div>
            
            <div id="ph-cal-step-2" class="hidden">
                <div class="alert alert-warning mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-sm"><strong>Bilas sensor</strong> dengan air suling, lalu celupkan ke <strong>buffer pH 6.86</strong></span>
                </div>
                <div class="text-center mb-4">
                    <div class="countdown text-warning" id="countdown-2">30</div>
                    <div class="text-sm opacity-70">detik</div>
                </div>
                <button class="btn btn-warning btn-block btn-sm" onclick="startCountdown(2, ${id})">
                    Mulai Countdown
                </button>
            </div>
            
            <div id="ph-cal-step-3" class="hidden">
                <div class="alert alert-success mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm">Masukkan nilai pembacaan sensor</span>
                </div>
                <div class="form-control mb-3">
                    <label class="label py-1"><span class="label-text text-sm">Pembacaan pH 4.01:</span></label>
                    <input type="number" id="ph401-reading-${id}" class="input input-bordered input-sm" step="0.01" placeholder="Contoh: 4.12">
                </div>
                <div class="form-control mb-4">
                    <label class="label py-1"><span class="label-text text-sm">Pembacaan pH 6.86:</span></label>
                    <input type="number" id="ph686-reading-${id}" class="input input-bordered input-sm" step="0.01" placeholder="Contoh: 6.95">
                </div>
                <button class="btn btn-success btn-block btn-sm" onclick="finishPhCalibration(${id})">
                    Selesai & Simpan
                </button>
            </div>
            
            <div class="modal-action mt-4">
                <button class="btn btn-ghost btn-sm" onclick="this.closest('.modal').remove()">Tutup</button>
            </div>
        </div>
        <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
    `;
    document.body.appendChild(modal);
}

function startCountdown(step, id) {
    let seconds = 30;
    const countdownEl = document.getElementById(`countdown-${step}`);
    countdownEl.classList.add('pulse-animation');
    
    const interval = setInterval(() => {
        seconds--;
        countdownEl.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(interval);
            countdownEl.classList.remove('pulse-animation');
            
            if (step === 1) {
                document.getElementById('ph-cal-step-1').classList.add('hidden');
                document.getElementById('ph-cal-step-2').classList.remove('hidden');
            } else if (step === 2) {
                document.getElementById('ph-cal-step-2').classList.add('hidden');
                document.getElementById('ph-cal-step-3').classList.remove('hidden');
            }
        }
    }, 1000);
}

async function finishPhCalibration(id) {
    const ph401 = document.getElementById(`ph401-reading-${id}`).value;
    const ph686 = document.getElementById(`ph686-reading-${id}`).value;
    
    if (!ph401 || !ph686) {
        showError('Mohon isi kedua nilai pembacaan');
        return;
    }
    
    try {
        const response = await fetch('<?= base_url('api/calibration/save-ph') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: id,
                ph401_reading: parseFloat(ph401),
                ph686_reading: parseFloat(ph686)
            })
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Kalibrasi pH berhasil!');
            document.querySelector('.modal')?.remove();
            loadCalibrationSettings();
        } else {
            showError(result.message || 'Gagal menyimpan kalibrasi pH');
        }
    } catch (error) {
        showError('Error: ' + error.message);
    }
}

async function clearPhCalibration(id) {
    if (!confirm('Yakin ingin menghapus kalibrasi pH?')) return;
    
    const data = {
        id: id,
        ph_slope: null,
        ph_intercept: null,
        active: 1
    };
    
    try {
        const response = await fetch('<?= base_url('api/calibration/update') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Kalibrasi pH dihapus');
            loadCalibrationSettings();
        }
    } catch (error) {
        showError('Error: ' + error.message);
    }
}

function showSuccess(message) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-top toast-center z-50';
    toast.innerHTML = `
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm">${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function showError(message) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-top toast-center z-50';
    toast.innerHTML = `
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm">${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
    document.getElementById('loading')?.classList.add('hidden');
}
</script>
<?= $this->endSection() ?>
