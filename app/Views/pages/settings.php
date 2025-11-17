<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto px-2 py-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card Pengaturan -->
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Pengaturan Sistem</h1>
                <p class="text-gray-500 mb-6">Kelola interval penyimpanan data telemetri ke database.</p>
                <form id="form-save-interval">
                    <label for="save_interval" class="block font-medium mb-2">Interval Penyimpanan</label>
                    <select class="select select-bordered w-full mb-3" id="save_interval" name="save_interval">
                        <option value="0" <?= $save_interval == 0 ? 'selected' : '' ?>>Simpan Semua Data (Real-time)</option>
                        <option value="1" <?= $save_interval == 1 ? 'selected' : '' ?>>1 Detik</option>
                        <option value="5" <?= $save_interval == 5 ? 'selected' : '' ?>>5 Detik</option>
                        <option value="60" <?= $save_interval == 60 ? 'selected' : '' ?>>1 Menit</option>
                        <option value="300" <?= $save_interval == 300 ? 'selected' : '' ?>>5 Menit</option>
                        <option value="600" <?= $save_interval == 600 ? 'selected' : '' ?>>10 Menit</option>
                        <option value="900" <?= $save_interval == 900 ? 'selected' : '' ?>>15 Menit</option>
                        <option value="1800" <?= $save_interval == 1800 ? 'selected' : '' ?>>30 Menit</option>
                        <option value="3600" <?= $save_interval == 3600 ? 'selected' : '' ?>>1 Jam</option>
                    </select>
                    <button type="submit" class="btn btn-success w-full">Simpan Pengaturan</button>
                </form>
            </div>
            <div class="mt-6 bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                <div class="font-semibold mb-1">Tips:</div>
                <ul class="list-disc ml-4">
                    <li>Interval pendek = data lebih detail, database cepat penuh</li>
                    <li>Interval panjang = hemat ruang database</li>
                    <li>Real-time = untuk monitoring langsung</li>
                </ul>
            </div>
            <div id="alert-container" class="mt-4"></div>
        </div>
        <!-- Card Status -->
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-2">Status Saat Ini</h2>
                <div class="mb-4">
                    <div class="text-gray-500 text-sm mb-1">Interval Aktif</div>
                    <div id="current-interval-display" class="font-semibold">
                        <?php
                        if ($save_interval == 0) {
                            echo '<span class="badge badge-success">Real-time</span>';
                        } else if ($save_interval < 60) {
                            echo '<span class="badge">' . $save_interval . ' Detik</span>';
                        } else {
                            $minutes = $save_interval / 60;
                            if ($minutes >= 60) {
                                $hours = $minutes / 60;
                                echo '<span class="badge">' . $hours . ' Jam</span>';
                            } else {
                                echo '<span class="badge">' . $minutes . ' Menit</span>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="text-gray-500 text-sm mb-1">Mode Penyimpanan</div>
                    <div id="mode-description" class="text-sm">
                        <?= $save_interval == 0 
                            ? 'Setiap data dari MQTT langsung disimpan' 
                            : ($save_interval < 60 
                                ? 'Data disimpan setiap ' . $save_interval . ' detik' 
                                : 'Data disimpan setiap ' . ($save_interval >= 3600 ? ($save_interval / 3600) . ' jam' : ($save_interval / 60) . ' menit')) 
                        ?>
                    </div>
                </div>
            </div>
            <div class="mt-6 bg-yellow-50 rounded-lg p-4 text-sm text-yellow-700">
                <div class="font-semibold mb-1">Perhatian</div>
                <div>Perubahan pengaturan berlaku langsung untuk data baru yang diterima.</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-save-interval');
    const selectInterval = document.getElementById('save_interval');
    const alertContainer = document.getElementById('alert-container');

    selectInterval.addEventListener('change', updateCurrentDisplay);

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Menyimpan...';

        const formData = new FormData(form);

        try {
            const response = await fetch('<?= base_url('settings/update-save-interval') ?>', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', result.message);
                updateCurrentDisplay();
            } else {
                showAlert('error', result.message || 'Gagal memperbarui pengaturan.');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat menyimpan pengaturan.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    });

    function updateCurrentDisplay() {
        const interval = parseInt(selectInterval.value);
        const displayEl = document.getElementById('current-interval-display');
        const modeEl = document.getElementById('mode-description');

        if (interval === 0) {
            displayEl.innerHTML = '<span class="badge badge-success">Real-time</span>';
            modeEl.textContent = 'Setiap data dari MQTT langsung disimpan';
        } else {
            const minutes = interval / 60;
            let label = '';
            let description = '';

            if (minutes >= 60) {
                const hours = minutes / 60;
                label = hours + ' Jam';
                description = `Data disimpan setiap ${hours} jam`;
            } else {
                label = minutes + ' Menit';
                description = `Data disimpan setiap ${minutes} menit`;
            }

            displayEl.innerHTML = `<span class="badge">${label}</span>`;
            modeEl.textContent = description;
        }
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass}`;
        alert.innerHTML = `<span>${message}</span>`;

        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);

        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 3000);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-save-interval');
    const selectInterval = document.getElementById('save_interval');
    const alertContainer = document.getElementById('alert-container');

    // Update display when selection changes
    selectInterval.addEventListener('change', updateCurrentDisplay);

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading loading-spinner"></span> Menyimpan...';

        const formData = new FormData(form);

        try {
            const response = await fetch('<?= base_url('settings/update-save-interval') ?>', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', '✅ ' + result.message);
                updateCurrentDisplay();
            } else {
                showAlert('error', '❌ ' + (result.message || 'Gagal memperbarui pengaturan.'));
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', '❌ Terjadi kesalahan saat menyimpan pengaturan.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    });

    function updateCurrentDisplay() {
        const interval = parseInt(selectInterval.value);
        const displayEl = document.getElementById('current-interval-display');
        const modeEl = document.getElementById('mode-description');

        if (interval === 0) {
            displayEl.innerHTML = '<span class="badge badge-success badge-lg gap-2"><span class="animate-pulse">●</span> Real-time</span>';
            modeEl.textContent = 'Setiap data dari MQTT langsung disimpan ke database';
        } else {
            const minutes = interval / 60;
            let label = '';
            let description = '';

            if (minutes >= 60) {
                const hours = minutes / 60;
                label = '⏱️ ' + hours + ' Jam';
                description = `Data disimpan setiap ${hours} jam`;
            } else {
                label = '⏱️ ' + minutes + ' Menit';
                description = `Data disimpan setiap ${minutes} menit`;
            }

            displayEl.innerHTML = `<span class="badge badge-accent badge-lg">${label}</span>`;
            modeEl.textContent = description;
        }
    }

    function showAlert(type, message) {
        const alertTypes = {
            'success': 'alert-success',
                    <?= $this->endSection() ?>
            'error': 'alert-error',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };

        const icons = {
            'success': '<svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'error': '<svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'warning': '<svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            'info': '<svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        };

        const alert = document.createElement('div');
        alert.className = `alert ${alertTypes[type]} shadow-lg animate-fadeIn`;
        alert.innerHTML = `
            ${icons[type]}
            <span>${message}</span>
            <button class="btn btn-sm btn-circle btn-ghost" onclick="this.parentElement.remove()">✕</button>
        `;

        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);

        // Smooth scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>
