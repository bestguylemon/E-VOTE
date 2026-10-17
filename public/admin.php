<?php 
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Admin - E-Voting OSIS</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="layout">
    <!-- Watermark Logo -->
  <div class="watermark-logo" aria-hidden="true"></div>

  <!-- ============ SIDEBAR ============ -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="tutupSidebar()"></div>
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-title">E-VOTING OSIS</div>
      <div class="brand-sub">Panel Admin</div>
    </div>

    <nav class="sidebar-nav">
      <button class="nav-item active" onclick="switchTab('hasil', this)" data-tab="hasil">
        <span class="nav-icon">▪</span> Hasil
      </button>
      <button class="nav-item" onclick="switchTab('history', this)" data-tab="history">
        <span class="nav-icon">▪</span> Riwayat
      </button>
      <button class="nav-item" onclick="switchTab('setting', this)" data-tab="setting">
        <span class="nav-icon">▪</span> Waktu
      </button>
      <button class="nav-item" onclick="switchTab('kandidat', this)" data-tab="kandidat">
        <span class="nav-icon">▪</span> Kandidat
      </button>
      <button class="nav-item" onclick="switchTab('siswa', this)" data-tab="siswa">
        <span class="nav-icon">▪</span> Siswa
      </button>
      <button class="nav-item" onclick="switchTab('webhook', this)" data-tab="webhook">
        <span class="nav-icon">▪</span> Webhook
      </button>
      <button class="nav-item" onclick="switchTab('password', this)" data-tab="password">
        <span class="nav-icon">▪</span> Akun
      </button>
    </nav>

    <div class="sidebar-footer">
      <button class="nav-item nav-logout" onclick="logoutAdmin()">
        <span class="nav-icon">▪</span> Keluar
      </button>
    </div>
  </aside>

  <!-- ============ MAIN AREA ============ -->
  <main class="main">

    <!-- Top bar -->
        <header class="topbar">
      <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Menu">☰</button>
      <div class="breadcrumb" id="breadcrumb">Panel Admin / Hasil</div>
      <div class="user-info">
        <span class="user-name" id="adminNama">-</span>
        <button class="btn-logout" onclick="logoutAdmin()">Keluar</button>
      </div>
    </header>

    <!-- Content -->
    <div class="content">

      <!-- ============ TAB: HASIL ============ -->
      <section id="tabHasil" class="tab-panel">
        <h2 class="page-title">Hasil Voting</h2>

        <div class="stat-row">
          <div class="stat-card">
            <div class="stat-value" id="totalSuara">0</div>
            <div class="stat-label">Total Suara</div>
          </div>
          <div class="stat-card">
            <div class="stat-value" id="lastUpdate">-</div>
            <div class="stat-label">Update Terakhir</div>
          </div>
        </div>

        <div class="card">
  <div class="card-header-actions">
    <div class="card-title" style="border: none; margin: 0; padding: 0;">Perolehan Suara</div>
    <div class="export-actions">
      <button class="btn-export" onclick="exportData('hasil')">
        ⬇ Export Hasil
      </button>
    </div>
  </div>
  <div id="hasilList" style="margin-top: 16px;"></div>
</div>
      </section>

      <!-- ============ TAB: RIWAYAT ============ -->
      <section id="tabHistory" class="tab-panel hidden">
        <h2 class="page-title">Riwayat Voting</h2>

        <div class="stat-row">
          <div class="stat-card">
            <div class="stat-value" id="histTotal">0</div>
            <div class="stat-label">Total Vote</div>
          </div>
          <div class="stat-card">
            <div class="stat-value" id="histIP">0</div>
            <div class="stat-label">IP Unik</div>
          </div>
          <div class="stat-card">
            <div class="stat-value" id="histLast">-</div>
            <div class="stat-label">Vote Terakhir</div>
          </div>
        </div>

        <div class="card">
  <div class="card-header-actions">
    <div class="card-title" style="border: none; margin: 0; padding: 0;">Riwayat Voting</div>
    <div class="export-actions">
      <button class="btn-export" onclick="exportData('riwayat')">
        ⬇ Export Riwayat
      </button>
    </div>
  </div>
  <div class="table-wrap" style="margin-top: 16px;">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>NIS</th>
                  <th>Nama Siswa</th>
                  <th>Pilihan</th>
                  <th>IP Address</th>
                  <th>Device</th>
                  <th>Waktu Submit</th>
                </tr>
              </thead>
              <tbody id="historyBody">
                <tr><td colspan="6" class="empty-state">Memuat...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ============ TAB: WAKTU ============ -->
      <section id="tabSetting" class="tab-panel hidden">
        <h2 class="page-title">Pengaturan Waktu Voting</h2>

        <div class="card" style="max-width:560px;">
          <div class="card-title">Periode Voting</div>

          <div class="form-group">
            <label>Waktu Mulai</label>
            <input type="datetime-local" id="setMulai">
          </div>

          <div class="form-group">
            <label>Waktu Selesai</label>
            <input type="datetime-local" id="setSelesai">
          </div>

          <div id="settingMsg"></div>

          <button class="btn-primary" onclick="simpanWaktu()">Simpan Pengaturan</button>

          <div class="info-box">
            Setelah disimpan, halaman voting akan menampilkan countdown sesuai waktu di atas.
          </div>

          <div id="statusInfo" style="text-align:center; margin-top:16px;"></div>
        </div>
      </section>

      <!-- ============ TAB: KANDIDAT ============ -->
      <section id="tabKandidat" class="tab-panel hidden">
        <h2 class="page-title">Kelola Kandidat</h2>

        <div class="card">
          <div class="card-title">Tambah Kandidat Baru</div>

          <div class="form-row">
            <div class="form-group">
              <label>Nama Kandidat</label>
              <input type="text" id="addNama" placeholder="Contoh: Ahmad Rizky">
            </div>
            <div class="form-group">
              <label>Kelas</label>
              <input type="text" id="addKelas" placeholder="XI IPA 1">
            </div>
          </div>

          <div class="form-group">
            <label>Visi (opsional)</label>
            <input type="text" id="addVisi" placeholder="OSIS Aktif & Kreatif">
          </div>

          <div id="addKandidatMsg"></div>
          <button class="btn-primary" onclick="tambahKandidat()">Tambah Kandidat</button>
        </div>

        <div class="card">
          <div class="card-title">Daftar Kandidat</div>
          <div id="kandidatList-admin"></div>
        </div>
      </section>

      <!-- ============ TAB: SISWA ============ -->
      <section id="tabSiswa" class="tab-panel hidden">
        <h2 class="page-title">Kelola Siswa</h2>

        <div class="card">
          <div class="card-title">Tambah Siswa Baru</div>

          <div class="form-row">
            <div class="form-group">
              <label>NIS</label>
              <input type="text" id="newNis" placeholder="Contoh: 12348">
            </div>
            <div class="form-group">
              <label>Kelas</label>
              <input type="text" id="newKelas" placeholder="X IPA 1">
            </div>
          </div>

          <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" id="newNama" placeholder="Budi Setiawan">
          </div>

          <div id="siswaMsg"></div>
          <button class="btn-primary" onclick="tambahSiswa()">Tambah Siswa</button>
        </div>

        <div class="stat-row">
          <div class="stat-card">
            <div class="stat-value" id="totalSiswa">0</div>
            <div class="stat-label">Total Siswa</div>
          </div>
          <div class="stat-card">
            <div class="stat-value" id="sudahVote">0</div>
            <div class="stat-label">Sudah Memilih</div>
          </div>
          <div class="stat-card">
            <div class="stat-value" id="belumVote">0</div>
            <div class="stat-label">Belum Memilih</div>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Daftar Siswa</div>

          <input type="text" id="searchSiswa" class="search-input"
            placeholder="Cari NIS / Nama / Kelas..." oninput="filterSiswa()">

          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>#</th><th>NIS</th><th>Nama</th><th>Kelas</th>
                  <th>Status</th><th>Aksi</th>
                </tr>
              </thead>
              <tbody id="siswaBody">
                <tr><td colspan="6" class="empty-state">Memuat...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ============ TAB: PASSWORD ============ -->
             <!-- ============ TAB: WEBHOOK ============ -->
      <section id="tabWebhook" class="tab-panel hidden">
        <h2 class="page-title">Manajemen Discord Webhook</h2>

        <!-- Form Tambah Webhook -->
        <div class="card">
          <div class="card-title">Tambah Webhook Baru</div>

          <div class="form-row">
            <div class="form-group">
              <label>Nama Webhook</label>
              <input type="text" id="whNama" placeholder="vote / admin / error">
            </div>
            <div class="form-group">
              <label>Deskripsi</label>
              <input type="text" id="whDeskripsi" placeholder="Notifikasi suara masuk">
            </div>
          </div>

          <div class="form-group">
            <label>URL Webhook Discord</label>
            <input type="text" id="whUrl" placeholder="https://discord.com/api/webhooks/...">
          </div>

          <div id="whAddMsg"></div>
          <button class="btn-primary" onclick="tambahWebhook()">Tambah Webhook</button>
        </div>

        <!-- Daftar Webhook -->
        <div class="card">
          <div class="card-header-actions">
            <div class="card-title" style="border: none; margin: 0; padding: 0;">Daftar Webhook</div>
            <div class="export-actions">
              <button class="btn-export" onclick="loadWebhook()">
                ↻ Refresh
              </button>
            </div>
          </div>
          <div id="webhookList" style="margin-top: 16px;"></div>
        </div>
      </section>
      <section id="tabPassword" class="tab-panel hidden">
        <h2 class="page-title">Ganti Password</h2>

        <div class="card" style="max-width:480px;">
          <div class="card-title">Password Admin</div>

          <div class="form-group">
            <label>Password Lama</label>
            <input type="password" id="pwLama" placeholder="••••••••">
          </div>

          <div class="form-group">
            <label>Password Baru</label>
            <input type="password" id="pwBaru" placeholder="Minimal 6 karakter">
          </div>

          <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <input type="password" id="pwKonfirm" placeholder="Ulangi password baru">
          </div>

          <div id="pwMsg"></div>
          <button class="btn-primary" onclick="gantiPassword()">Simpan Password</button>

          <div class="info-box">
            Password minimal 6 karakter. Setelah diganti, gunakan password baru untuk login berikutnya.
          </div>
        </div>
      </section>

    </div>
  </main>

</div>

<script>
// ============================================
// JS — SAMA PERSIS, TIDAK BERUBAH
// ============================================
var API = '../api/handler.php';

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str).replace(/[&<>"']/g, function(s) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
  });
}

function formatTime(timestamp) {
  if (!timestamp) return '-';
  var d = new Date(timestamp);
  var tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  var jam = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  return tgl + ' ' + jam;
}

async function cekAdmin() {
  try {
    var res = await fetch(API + '?action=check_admin');
    var data = await res.json();
    if (data.success) {
      var el = document.getElementById('adminNama');
      if (el) el.textContent = data.nama;
    } else {
      window.location.href = 'login.php';
    }
  } catch (e) { console.error(e); }
}

function toggleSidebar() {
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  if (sidebar) sidebar.classList.toggle('open');
  if (overlay) overlay.classList.toggle('active');
}

function tutupSidebar() {
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  if (sidebar) sidebar.classList.remove('open');
  if (overlay) overlay.classList.remove('active');
}

// Tutup sidebar kalau layar di-resize ke desktop
window.addEventListener('resize', function() {
  if (window.innerWidth > 768) {
    tutupSidebar();
  }
});

function exportData(format) {
  window.open('export.php?format=' + format, '_blank');
}

async function logoutAdmin() {
  if (!confirm('Yakin mau logout?')) return;
  try { await fetch(API + '?action=logout_admin'); } catch (e) {}
  window.location.href = 'login.php';
}

function switchTab(tab, el) {
  document.querySelectorAll('.nav-item').forEach(function(t) { t.classList.remove('active'); });
  el.classList.add('active');
    // Auto-close sidebar di mobile
  if (window.innerWidth <= 768) {
    tutupSidebar();
  }

  document.getElementById('tabHasil').classList.toggle('hidden', tab !== 'hasil');
  document.getElementById('tabHistory').classList.toggle('hidden', tab !== 'history');
  document.getElementById('tabSetting').classList.toggle('hidden', tab !== 'setting');
  document.getElementById('tabKandidat').classList.toggle('hidden', tab !== 'kandidat');
  document.getElementById('tabSiswa').classList.toggle('hidden', tab !== 'siswa');
  document.getElementById('tabPassword').classList.toggle('hidden', tab !== 'password');
    document.getElementById('tabWebhook').classList.toggle('hidden', tab !== 'webhook');

  // Update breadcrumb
  var labels = {
    hasil: 'Hasil', history: 'Riwayat', setting: 'Waktu',
    kandidat: 'Kandidat', siswa: 'Siswa', password: 'Akun'
  };
  var bc = document.getElementById('breadcrumb');
  if (bc) bc.textContent = 'Panel Admin / ' + (labels[tab] || tab);

  if (tab === 'history') loadHistory();
  if (tab === 'setting') loadPengaturan();
  if (tab === 'kandidat') loadKandidatAdmin();
  if (tab === 'webhook') loadWebhook();
  if (tab === 'siswa') loadSiswa();
}

async function loadHasil() {
  try {
    var res = await fetch(API + '?action=hasil');
    var data = await res.json();
    if (!data.success) return;

    document.getElementById('totalSuara').textContent = data.total;
    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('id-ID');

    document.getElementById('hasilList').innerHTML = data.data.map(function(k) {
      var persen = data.total > 0 ? ((k.suara / data.total) * 100).toFixed(1) : 0;
      return '<div class="hasil-item">' +
        '<div class="hasil-head">' +
        '<div><strong>' + escapeHtml(k.nama) + '</strong>' +
        '<div class="hasil-kelas">' + escapeHtml(k.kelas) + '</div></div>' +
        '<div class="hasil-suara">' + k.suara + ' suara (' + persen + '%)</div>' +
        '</div>' +
        '<div class="progress"><div class="progress-fill" style="width:' + persen + '%"></div></div>' +
        '</div>';
    }).join('');
  } catch (e) { console.error(e); }
}

async function loadHistory() {
  try {
    var res = await fetch(API + '?action=history');
    var data = await res.json();
    if (!data.success) return;

    var history = data.history;
    var tbody = document.getElementById('historyBody');
    document.getElementById('histTotal').textContent = history.length;

    var ips = {};
    history.forEach(function(h) { ips[h.ip_address] = true; });
    document.getElementById('histIP').textContent = Object.keys(ips).length;
    document.getElementById('histLast').textContent = history.length > 0 ? formatTime(history[0].waktu) : '-';

    if (history.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Belum ada suara yang masuk</td></tr>';
      return;
    }

    tbody.innerHTML = history.map(function(h, i) {
      return '<tr>' +
        '<td>' + (i + 1) + '</td>' +
        '<td><strong>' + escapeHtml(h.nis) + '</strong></td>' +
        '<td>' + escapeHtml(h.nama_siswa) + '<div class="sub">' + escapeHtml(h.kelas_siswa || '-') + '</div></td>' +
        '<td><strong>' + escapeHtml(h.nama_kandidat) + '</strong><div class="sub">' + escapeHtml(h.kelas_kandidat || '') + '</div></td>' +
        '<td><span class="badge">' + escapeHtml(h.ip_address || '-') + '</span></td>' +
        '<td><span class="badge">' + escapeHtml(h.device_id || '-') + '</span></td>' +
        '<td>' + formatTime(h.waktu) + '</td>' +
        '</tr>';
    }).join('');
  } catch (e) { console.error(e); }
}

async function loadPengaturan() {
  try {
    var res = await fetch(API + '?action=pengaturan');
    var data = await res.json();
    if (!data.success) return;

    if (data.mulai) document.getElementById('setMulai').value = data.mulai.replace(' ', 'T').substring(0, 16);
    if (data.selesai) document.getElementById('setSelesai').value = data.selesai.replace(' ', 'T').substring(0, 16);

    var statusEl = document.getElementById('statusInfo');
    var cls = 'status-aktif', txt = 'Voting Sedang Berlangsung';
    if (data.status === 'belum_mulai') { cls = 'status-belum'; txt = 'Voting Belum Dimulai'; }
    else if (data.status === 'selesai') { cls = 'status-selesai'; txt = 'Voting Telah Ditutup'; }
    statusEl.innerHTML = '<span class="status-pill ' + cls + '">' + txt + '</span>';
  } catch (e) { console.error(e); }
}

async function simpanWaktu() {
  var mulai = document.getElementById('setMulai').value;
  var selesai = document.getElementById('setSelesai').value;
  var msg = document.getElementById('settingMsg');

  if (!mulai || !selesai) { msg.innerHTML = '<div class="alert alert-error">Isi kedua waktu!</div>'; return; }
  if (new Date(selesai) <= new Date(mulai)) { msg.innerHTML = '<div class="alert alert-error">Waktu selesai harus setelah waktu mulai!</div>'; return; }

  try {
    var fd = new FormData();
    fd.append('action', 'set_pengaturan');
    fd.append('mulai', mulai.replace('T', ' ') + ':00');
    fd.append('selesai', selesai.replace('T', ' ') + ':00');

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success">Pengaturan waktu tersimpan</div>';
      setTimeout(function() { msg.innerHTML = ''; }, 3000);
      loadPengaturan();
    } else {
      msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
  }
}

async function loadKandidatAdmin() {
  try {
    var res = await fetch(API + '?action=hasil');
    var data = await res.json();
    if (!data.success) return;

    var detailList = await Promise.all(data.data.map(async function(k) {
      var r = await fetch(API + '?action=detail_kandidat&id=' + k.id);
      var d = await r.json();
      return d.success ? d.kandidat : k;
    }));

    renderKandidatAdmin(detailList);
  } catch (e) { console.error(e); }
}

function renderKandidatAdmin(list) {
  var wrap = document.getElementById('kandidatList-admin');

  if (list.length === 0) {
    wrap.innerHTML = '<div class="empty-state">Belum ada kandidat. Tambahkan di atas.</div>';
    return;
  }

  wrap.innerHTML = list.map(function(k, idx) {
    var fotoHtml = k.foto
      ? '<img src="' + escapeHtml(k.foto) + '">'
      : escapeHtml(k.nama.charAt(0));

    return '<div class="kandidat-card">' +
      '<div class="kandidat-head">' +
      '<div>' +
      '<strong>Kandidat #' + (idx + 1) + ' &mdash; ' + escapeHtml(k.nama) + '</strong>' +
      '<div class="sub">' + escapeHtml(k.kelas) + '</div>' +
      '</div>' +
      '<button class="btn-danger" onclick="hapusKandidat(' + k.id + ', \'' + escapeHtml(k.nama).replace(/'/g, "\\'") + '\')">Hapus</button>' +
      '</div>' +

      '<div class="foto-row">' +
      '<div class="foto-preview" id="preview-' + k.id + '">' + fotoHtml + '</div>' +
      '<div style="flex:1;">' +
      '<input type="file" id="file-' + k.id + '" accept="image/*" onchange="uploadFoto(' + k.id + ')" class="file-input">' +
      '<div class="hint">JPG/PNG/WEBP, maks 2 MB</div>' +
      '</div></div>' +

      '<div class="form-group"><label>Nama</label><input type="text" id="nama-' + k.id + '" value="' + escapeHtml(k.nama) + '"></div>' +
      '<div class="form-row">' +
      '<div class="form-group"><label>Kelas</label><input type="text" id="kelas-' + k.id + '" value="' + escapeHtml(k.kelas) + '"></div>' +
      '<div class="form-group"><label>TTL</label><input type="text" id="ttl-' + k.id + '" value="' + escapeHtml(k.ttl || '') + '" placeholder="Jakarta, 1 Jan 2008"></div>' +
      '</div>' +
      '<div class="form-group"><label>Visi</label><input type="text" id="visi-' + k.id + '" value="' + escapeHtml(k.visi || '') + '"></div>' +
      '<div class="form-group"><label>Misi (satu per baris)</label><textarea id="misi-' + k.id + '">' + escapeHtml(k.misi || '') + '</textarea></div>' +
      '<div class="form-group"><label>Program Kerja (satu per baris)</label><textarea id="program-' + k.id + '">' + escapeHtml(k.program || '') + '</textarea></div>' +
      '<div class="form-group"><label>Quote / Slogan</label><input type="text" id="quote-' + k.id + '" value="' + escapeHtml(k.quote || '') + '"></div>' +
      '<div class="form-group"><label>Motivasi</label><textarea id="motivasi-' + k.id + '">' + escapeHtml(k.motivasi || '') + '</textarea></div>' +

      '<button class="btn-primary" onclick="simpanProfil(' + k.id + ')">Simpan Profil</button>' +
      '<div id="msg-' + k.id + '" style="margin-top:8px;"></div>' +
      '</div>';
  }).join('');
}

async function tambahKandidat() {
  var nama = document.getElementById('addNama').value.trim();
  var kelas = document.getElementById('addKelas').value.trim();
  var visi = document.getElementById('addVisi').value.trim();
  var msg = document.getElementById('addKandidatMsg');

  msg.innerHTML = '';

  if (!nama || !kelas) {
    msg.innerHTML = '<div class="alert alert-error">Nama dan Kelas wajib diisi</div>';
    return;
  }

  try {
    var fd = new FormData();
    fd.append('action', 'tambah_kandidat');
    fd.append('nama', nama);
    fd.append('kelas', kelas);
    fd.append('visi', visi);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
      document.getElementById('addNama').value = '';
      document.getElementById('addKelas').value = '';
      document.getElementById('addVisi').value = '';
      document.getElementById('addNama').focus();
      loadKandidatAdmin();
      setTimeout(function() { msg.innerHTML = ''; }, 3000);
    } else {
      msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
  }
}

async function hapusKandidat(id, nama) {
  if (!confirm('Yakin hapus kandidat:\n\n' + nama + '\n\nData yang sudah dihapus tidak bisa dikembalikan.')) return;

  try {
    var fd = new FormData();
    fd.append('action', 'hapus_kandidat');
    fd.append('id', id);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) { alert(data.message); loadKandidatAdmin(); }
    else { alert(data.message); }
  } catch (e) { alert('Error: ' + e.message); }
}

async function simpanProfil(id) {
  var msg = document.getElementById('msg-' + id);
  msg.innerHTML = '<div class="alert alert-success">Menyimpan...</div>';

  try {
    var fd = new FormData();
    fd.append('action', 'update_kandidat');
    fd.append('id', id);
    fd.append('nama', document.getElementById('nama-' + id).value);
    fd.append('kelas', document.getElementById('kelas-' + id).value);
    fd.append('ttl', document.getElementById('ttl-' + id).value);
    fd.append('visi', document.getElementById('visi-' + id).value);
    fd.append('misi', document.getElementById('misi-' + id).value);
    fd.append('program', document.getElementById('program-' + id).value);
    fd.append('quote', document.getElementById('quote-' + id).value);
    fd.append('motivasi', document.getElementById('motivasi-' + id).value);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success">Tersimpan</div>';
      setTimeout(function() { msg.innerHTML = ''; }, 2500);
    } else {
      msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
  }
}

async function uploadFoto(id) {
  var input = document.getElementById('file-' + id);
  var msg = document.getElementById('msg-' + id);
  if (!input.files || !input.files[0]) return;

  msg.innerHTML = '<div class="alert alert-success">Mengupload foto...</div>';

  try {
    var fd = new FormData();
    fd.append('action', 'upload_foto');
    fd.append('id', id);
    fd.append('foto', input.files[0]);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      document.getElementById('preview-' + id).innerHTML = '<img src="' + data.foto + '">';
      msg.innerHTML = '<div class="alert alert-success">Foto berhasil diupload</div>';
      setTimeout(function() { msg.innerHTML = ''; }, 2500);
    } else {
      msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
  }
}

var siswaList = [];

async function loadSiswa() {
  try {
    var res = await fetch(API + '?action=list_siswa');
    var data = await res.json();
    if (!data.success) return;
    siswaList = data.siswa;
    renderSiswa(siswaList);
    updateSiswaStats();
  } catch (e) { console.error(e); }
}

function updateSiswaStats() {
  var total = siswaList.length;
  var sudah = siswaList.filter(function(s) { return s.sudah_memilih; }).length;
  document.getElementById('totalSiswa').textContent = total;
  document.getElementById('sudahVote').textContent = sudah;
  document.getElementById('belumVote').textContent = total - sudah;
}

function renderSiswa(list) {
  var tbody = document.getElementById('siswaBody');

  if (list.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Tidak ada siswa</td></tr>';
    return;
  }

  tbody.innerHTML = list.map(function(s, i) {
    var badge = s.sudah_memilih
      ? '<span class="badge badge-success">Sudah</span>'
      : '<span class="badge badge-warning">Belum</span>';
    return '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td><strong>' + escapeHtml(s.nis) + '</strong></td>' +
      '<td>' + escapeHtml(s.nama) + '</td>' +
      '<td>' + escapeHtml(s.kelas || '-') + '</td>' +
      '<td>' + badge + '</td>' +
      '<td>' +
'<button class="btn-sm btn-sm-edit" onclick="editSiswa(\'' + escapeHtml(s.nis) + '\')">Edit</button> ' +
'<button class="btn-sm btn-sm-hapus" onclick="hapusSiswa(\'' + escapeHtml(s.nis) + '\')">Hapus</button>' +
(s.sudah_memilih
  ? ' <button class="btn-sm btn-sm-reset" onclick="resetVote(\'' + escapeHtml(s.nis) + '\', \'' + escapeHtml(s.nama).replace(/'/g, "\\'") + '\')">Reset Vote</button>'
  : '') +
'</td></tr>';
  }).join('');
}

function filterSiswa() {
  var q = document.getElementById('searchSiswa').value.toLowerCase().trim();
  if (!q) { renderSiswa(siswaList); return; }
  var filtered = siswaList.filter(function(s) {
    return s.nis.toLowerCase().indexOf(q) >= 0 ||
           s.nama.toLowerCase().indexOf(q) >= 0 ||
           (s.kelas || '').toLowerCase().indexOf(q) >= 0;
  });
  renderSiswa(filtered);
}

async function tambahSiswa() {
  var nis = document.getElementById('newNis').value.trim();
  var nama = document.getElementById('newNama').value.trim();
  var kelas = document.getElementById('newKelas').value.trim();
  var msg = document.getElementById('siswaMsg');

  if (!nis || !nama) {
    msg.innerHTML = '<div class="alert alert-error">NIS dan Nama wajib diisi</div>';
    return;
  }

  try {
    var fd = new FormData();
    fd.append('action', 'tambah_siswa');
    fd.append('nis', nis);
    fd.append('nama', nama);
    fd.append('kelas', kelas);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
      document.getElementById('newNis').value = '';
      document.getElementById('newNama').value = '';
      document.getElementById('newKelas').value = '';
      document.getElementById('newNis').focus();
      loadSiswa();
      setTimeout(function() { msg.innerHTML = ''; }, 3000);
    } else {
      msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
  }
}

async function editSiswa(nis) {
  var s = siswaList.find(function(x) { return x.nis === nis; });
  if (!s) return;

  var namaBaru = prompt('Edit Nama untuk NIS ' + nis + ':', s.nama);
  if (namaBaru === null) return;
  if (!namaBaru.trim()) { alert('Nama tidak boleh kosong'); return; }

  var kelasBaru = prompt('Edit Kelas untuk ' + namaBaru + ':', s.kelas || '');
  if (kelasBaru === null) return;

  try {
    var fd = new FormData();
    fd.append('action', 'update_siswa');
    fd.append('nis', nis);
    fd.append('nama', namaBaru.trim());
    fd.append('kelas', kelasBaru.trim());

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) { alert(data.message); loadSiswa(); }
    else { alert(data.message); }
  } catch (e) { alert('Error: ' + e.message); }
}

async function hapusSiswa(nis) {
  var s = siswaList.find(function(x) { return x.nis === nis; });
  if (!s) return;

  if (s.sudah_memilih) {
    alert('Siswa ini sudah memilih, tidak bisa dihapus.');
    return;
  }

  if (!confirm('Yakin hapus siswa:\n\nNIS: ' + s.nis + '\nNama: ' + s.nama)) return;

  try {
    var fd = new FormData();
    fd.append('action', 'hapus_siswa');
    fd.append('nis', nis);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) { alert(data.message); loadSiswa(); }
    else { alert(data.message); }
  } catch (e) { alert('Error: ' + e.message); }
}
async function resetVote(nis, nama) {
  if (!confirm('Yakin hapus vote siswa ini?\n\nNIS: ' + nis + '\nNama: ' + nama + '\n\nSiswa akan bisa memilih ulang.')) {
    return;
  }

  try {
    var fd = new FormData();
    fd.append('action', 'reset_vote_siswa');
    fd.append('nis', nis);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      alert('✅ ' + data.message);
      loadSiswa();
    } else {
      alert('❌ ' + data.message);
    }
  } catch (e) {
    alert('Error: ' + e.message);
  }
}
  // ============================================
  // WEBHOOK MANAGEMENT
  // ============================================
  async function loadWebhook() {
    try {
      var res = await fetch(API + '?action=list_webhook');
      var data = await res.json();
      if (!data.success) return;

      renderWebhook(data.webhooks);
    } catch (e) {
      console.error(e);
      document.getElementById('webhookList').innerHTML =
        '<div class="empty-state">Gagal memuat webhook</div>';
    }
  }

  function renderWebhook(list) {
    var wrap = document.getElementById('webhookList');

    if (list.length === 0) {
      wrap.innerHTML = '<div class="empty-state">Belum ada webhook. Tambahkan di atas.</div>';
      return;
    }

    wrap.innerHTML = list.map(function(w) {
      var statusBadge = w.aktif
        ? '<span class="badge badge-success">Aktif</span>'
        : '<span class="badge badge-warning">Nonaktif</span>';

      return '<div class="kandidat-card" style="border-left-color: ' + (w.aktif ? '#2e7d32' : '#9e9e9e') + ';">' +
        '<div class="kandidat-head">' +
        '<div>' +
        '<strong>' + escapeHtml(w.nama) + ' ' + statusBadge + '</strong>' +
        '<div class="sub">' + escapeHtml(w.deskripsi || '-') + '</div>' +
        '</div>' +
        '<div style="display:flex; gap:6px; flex-wrap:wrap;">' +
        '<button class="btn-sm btn-sm-edit" onclick="testWebhook(' + w.id + ', \'' + escapeHtml(w.nama).replace(/'/g, "\\'") + '\')">Test</button> ' +
        '<button class="btn-sm" style="background:#1976d2;color:#fff;" onclick="editWebhook(' + w.id + ')">Edit</button> ' +
        '<button class="btn-sm" style="background:' + (w.aktif ? '#e65100' : '#2e7d32') + ';color:#fff;" onclick="toggleWebhook(' + w.id + ')">' + (w.aktif ? 'Matikan' : 'Aktifkan') + '</button> ' +
        '<button class="btn-sm btn-sm-hapus" onclick="hapusWebhook(' + w.id + ', \'' + escapeHtml(w.nama).replace(/'/g, "\\'") + '\')">Hapus</button>' +
        '</div>' +
        '</div>' +

        '<div class="form-group" style="margin-top:12px;">' +
        '<label>URL Webhook</label>' +
        '<input type="text" id="whUrl-' + w.id + '" value="' + escapeHtml(w.url) + '" style="font-family:Consolas,monospace;font-size:11px;">' +
        '</div>' +

        '<div class="form-row">' +
        '<div class="form-group">' +
        '<label>Nama</label>' +
        '<input type="text" id="whNama-' + w.id + '" value="' + escapeHtml(w.nama) + '">' +
        '</div>' +
        '<div class="form-group">' +
        '<label>Deskripsi</label>' +
        '<input type="text" id="whDesk-' + w.id + '" value="' + escapeHtml(w.deskripsi || '') + '">' +
        '</div>' +
        '</div>' +

        '<button class="btn-primary" onclick="simpanWebhook(' + w.id + ')" style="padding:8px;">Simpan Perubahan</button>' +
        '<div id="whMsg-' + w.id + '" style="margin-top:8px;"></div>' +
        '</div>';
    }).join('');
  }

  async function tambahWebhook() {
    var nama = document.getElementById('whNama').value.trim();
    var desk = document.getElementById('whDeskripsi').value.trim();
    var url  = document.getElementById('whUrl').value.trim();
    var msg  = document.getElementById('whAddMsg');

    msg.innerHTML = '';

    if (!nama || !url) {
      msg.innerHTML = '<div class="alert alert-error">Nama dan URL wajib diisi</div>';
      return;
    }

    try {
      var fd = new FormData();
      fd.append('action', 'tambah_webhook');
      fd.append('nama', nama);
      fd.append('deskripsi', desk);
      fd.append('url', url);

      var res = await fetch(API, { method: 'POST', body: fd });
      var data = await res.json();

      if (data.success) {
        msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
        document.getElementById('whNama').value = '';
        document.getElementById('whDeskripsi').value = '';
        document.getElementById('whUrl').value = '';
        loadWebhook();
        setTimeout(function() { msg.innerHTML = ''; }, 3000);
      } else {
        msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
      }
    } catch (e) {
      msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
    }
  }

  async function simpanWebhook(id) {
    var msg  = document.getElementById('whMsg-' + id);
    var nama = document.getElementById('whNama-' + id).value.trim();
    var desk = document.getElementById('whDesk-' + id).value.trim();
    var url  = document.getElementById('whUrl-' + id).value.trim();

    msg.innerHTML = '<div class="alert alert-success">Menyimpan...</div>';

    try {
      var fd = new FormData();
      fd.append('action', 'update_webhook');
      fd.append('id', id);
      fd.append('nama', nama);
      fd.append('deskripsi', desk);
      fd.append('url', url);

      var res = await fetch(API, { method: 'POST', body: fd });
      var data = await res.json();

      if (data.success) {
        msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
        setTimeout(function() { msg.innerHTML = ''; }, 3000);
        loadWebhook();
      } else {
        msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
      }
    } catch (e) {
      msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
    }
  }

  async function toggleWebhook(id) {
    try {
      var fd = new FormData();
      fd.append('action', 'toggle_webhook');
      fd.append('id', id);

      var res = await fetch(API, { method: 'POST', body: fd });
      var data = await res.json();

      if (data.success) {
        alert('✅ ' + data.message);
        loadWebhook();
      } else {
        alert('❌ ' + data.message);
      }
    } catch (e) {
      alert('Error: ' + e.message);
    }
  }

  async function hapusWebhook(id, nama) {
    if (!confirm('Yakin hapus webhook?\n\nNama: ' + nama + '\n\nData yang dihapus tidak bisa dikembalikan.')) return;

    try {
      var fd = new FormData();
      fd.append('action', 'hapus_webhook');
      fd.append('id', id);

      var res = await fetch(API, { method: 'POST', body: fd });
      var data = await res.json();

      if (data.success) {
        alert('✅ ' + data.message);
        loadWebhook();
      } else {
        alert('❌ ' + data.message);
      }
    } catch (e) {
      alert('Error: ' + e.message);
    }
  }

  async function testWebhook(id, nama) {
    if (!confirm('Kirim pesan test ke webhook "' + nama + '"?\n\nPesan akan muncul di channel Discord Anda.')) return;

    var btn = event.target;
    var originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Mengirim...';

    try {
      var fd = new FormData();
      fd.append('action', 'test_webhook');
      fd.append('id', id);

      var res = await fetch(API, { method: 'POST', body: fd });
      var data = await res.json();

      btn.disabled = false;
      btn.textContent = originalText;

      if (data.success) {
        alert('✅ ' + data.message);
      } else {
        alert('❌ ' + data.message);
      }
    } catch (e) {
      btn.disabled = false;
      btn.textContent = originalText;
      alert('Error: ' + e.message);
    }
  }

  function editWebhook(id) {
    var el = document.getElementById('whUrl-' + id);
    if (el) {
      el.focus();
      el.select();
    }
  }

async function gantiPassword() {
  var lama = document.getElementById('pwLama').value;
  var baru = document.getElementById('pwBaru').value;
  var konfirm = document.getElementById('pwKonfirm').value;
  var msg = document.getElementById('pwMsg');

  msg.innerHTML = '';

  if (!lama || !baru || !konfirm) { msg.innerHTML = '<div class="alert alert-error">Semua field wajib diisi</div>'; return; }
  if (baru.length < 6) { msg.innerHTML = '<div class="alert alert-error">Password baru minimal 6 karakter</div>'; return; }
  if (baru !== konfirm) { msg.innerHTML = '<div class="alert alert-error">Konfirmasi password tidak cocok</div>'; return; }
  if (lama === baru) { msg.innerHTML = '<div class="alert alert-error">Password baru harus berbeda dari password lama</div>'; return; }

  try {
    var fd = new FormData();
    fd.append('action', 'ganti_password');
    fd.append('password_lama', lama);
    fd.append('password_baru', baru);
    fd.append('konfirmasi', konfirm);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success">Password berhasil diganti</div>';
      document.getElementById('pwLama').value = '';
      document.getElementById('pwBaru').value = '';
      document.getElementById('pwKonfirm').value = '';
      setTimeout(function() { msg.innerHTML = ''; }, 5000);
    } else {
      msg.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="alert alert-error">Error: ' + e.message + '</div>';
  }
}

cekAdmin();
loadHasil();

setInterval(function() {
  loadHasil();
  var histTab = document.getElementById('tabHistory');
  if (histTab && !histTab.classList.contains('hidden')) {
    loadHistory();
  }
}, 5000);
</script>
</body>
</html>