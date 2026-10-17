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
<title>Admin - E-Voting OSIS</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.admin-wrap { max-width: 900px; }
.tabs { display: flex; gap: 6px; margin-bottom: 20px; border-bottom: 2px solid #c8e6c9; flex-wrap: wrap; }
.tab { padding: 10px 16px; cursor: pointer; font-size: 13px; font-weight: 600; color: #66bb6a; border: none; background: transparent; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.25s; font-family: inherit; white-space: nowrap; }
.tab.active { color: #1b5e20; border-bottom-color: #2e7d32; }
.tab:hover { color: #2e7d32; }

.history-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.history-table th { background: #2e7d32; color: #fff; padding: 10px 8px; text-align: left; font-weight: 600; font-size: 12px; position: sticky; top: 0; z-index: 2; }
.history-table td { padding: 10px 8px; border-bottom: 1px solid #e8f5e9; color: #333; }
.history-table tr:hover td { background: #f1f8e9; }
.history-table tr:nth-child(even) td { background: #f9fdf9; }

.badge-ip { display: inline-block; background: #e8f5e9; color: #1b5e20; padding: 3px 8px; border-radius: 4px; font-family: Consolas, monospace; font-size: 11px; }
.badge-time { display: inline-block; background: #fff3e0; color: #e65100; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
.history-scroll { max-height: 500px; overflow-y: auto; border: 1px solid #c8e6c9; border-radius: 10px; }
.history-scroll::-webkit-scrollbar { width: 8px; }
.history-scroll::-webkit-scrollbar-track { background: #f1f8e9; }
.history-scroll::-webkit-scrollbar-thumb { background: #a5d6a7; border-radius: 4px; }

.empty-state { text-align: center; padding: 40px 20px; color: #66bb6a; font-size: 14px; }
.stats-mini { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.stat-mini { flex: 1; min-width: 120px; background: #f1f8e9; border-radius: 8px; padding: 10px 14px; border-left: 3px solid #43a047; }
.stat-mini .val { color: #1b5e20; font-size: 20px; font-weight: bold; }
.stat-mini .lbl { color: #66bb6a; font-size: 11px; margin-top: 2px; }

.setting-box { background: #f9fdf9; border-radius: 12px; padding: 24px; border-left: 4px solid #2e7d32; }
.setting-box h3 { color: #1b5e20; font-size: 16px; margin-bottom: 20px; }
.setting-box label { display: block; color: #2e7d32; font-size: 13px; font-weight: 600; margin-bottom: 6px; margin-top: 14px; }
.setting-box input[type="text"], .setting-box input[type="password"], .setting-box input[type="datetime-local"], .setting-box input[type="file"] { width: 100%; padding: 12px 14px; border: 2px solid #c8e6c9; border-radius: 8px; font-size: 14px; outline: none; background: #fff; transition: all 0.25s; font-family: inherit; box-sizing: border-box; }
.setting-box input:focus { border-color: #43a047; }
.setting-info { background: #e8f5e9; border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #2e7d32; margin-top: 16px; }
.status-pill { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 10px; }
.status-aktif { background: #c8e6c9; color: #1b5e20; }
.status-belum { background: #fff3e0; color: #e65100; }
.status-selesai { background: #ffcdd2; color: #b71c1c; }

.kandidat-card { background: #f9fdf9; border-radius: 12px; padding: 16px; margin-bottom: 14px; border-left: 4px solid #2e7d32; }
.kandidat-card h3 { color: #1b5e20; font-size: 15px; margin-bottom: 4px; }
.kandidat-card small { color: #66bb6a; font-size: 12px; display: block; margin-bottom: 12px; }
.kandidat-card .form-group { margin-bottom: 12px; }
.kandidat-card label { font-size: 12px; margin-bottom: 4px; }
.kandidat-card input, .kandidat-card textarea { width: 100%; padding: 9px 12px; border: 2px solid #c8e6c9; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit; background: #fff; transition: border-color 0.2s; box-sizing: border-box; }
.kandidat-card input:focus, .kandidat-card textarea:focus { border-color: #43a047; }
.kandidat-card textarea { resize: vertical; min-height: 60px; }
.kandidat-card .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

.foto-preview { width: 80px; height: 80px; border-radius: 50%; background: #2e7d32; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; overflow: hidden; flex-shrink: 0; border: 3px solid #c8e6c9; }
.foto-preview img { width: 100%; height: 100%; object-fit: cover; }
.foto-row { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }

.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.btn-action { border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px; font-family: inherit; }
.btn-edit { background: #2e7d32; color: #fff; margin-right: 4px; }
.btn-hapus { background: #c62828; color: #fff; }

@media (max-width: 600px) {
  .tabs { gap: 4px; }
  .tab { padding: 8px 12px; font-size: 12px; }
}
</style>
</head>
<body>

<div class="container admin-wrap">
  <div class="header">
    <div class="logo">📊</div>
    <h1>Panel Admin</h1>
    <p class="subtitle">E-Voting OSIS</p>
  </div>

  <div class="tabs">
    <button class="tab active" onclick="switchTab('hasil', this)">📈 Hasil</button>
    <button class="tab" onclick="switchTab('history', this)">🕐 Riwayat</button>
    <button class="tab" onclick="switchTab('setting', this)">⚙️ Waktu</button>
    <button class="tab" onclick="switchTab('kandidat', this)">👥 Kandidat</button>
    <button class="tab" onclick="switchTab('siswa', this)">👨‍🎓 Siswa</button>
    <button class="tab" onclick="switchTab('password', this)">🔑 Password</button>
  </div>

  <div id="tabHasil">
    <div class="stat">
      <div class="stat-box">
        <div class="num" id="totalSuara">0</div>
        <div class="lbl">Total Suara</div>
      </div>
      <div class="stat-box">
        <div class="num" id="lastUpdate">-</div>
        <div class="lbl">Update Terakhir</div>
      </div>
    </div>
    <div id="hasilList"></div>
  </div>

  <div id="tabHistory" class="hidden">
    <div class="stats-mini">
      <div class="stat-mini">
        <div class="val" id="histTotal">0</div>
        <div class="lbl">Total Vote</div>
      </div>
      <div class="stat-mini">
        <div class="val" id="histIP">0</div>
        <div class="lbl">IP Unik</div>
      </div>
      <div class="stat-mini">
        <div class="val" id="histLast">-</div>
        <div class="lbl">Vote Terakhir</div>
      </div>
    </div>
    <div class="history-scroll">
      <table class="history-table">
        <thead>
          <tr>
            <th>#</th><th>NIS</th><th>Nama Siswa</th>
            <th>Pilihan</th><th>IP Address</th><th>Waktu Submit</th>
          </tr>
        </thead>
        <tbody id="historyBody">
          <tr><td colspan="6" class="empty-state">Memuat...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div id="tabSetting" class="hidden">
    <div class="setting-box">
      <h3>⏱️ Pengaturan Waktu Voting</h3>
      <label>Waktu Mulai</label>
      <input type="datetime-local" id="setMulai">
      <label>Waktu Selesai</label>
      <input type="datetime-local" id="setSelesai">
      <div id="settingMsg" style="margin-top:14px;"></div>
      <button class="btn" onclick="simpanWaktu()" style="margin-top:16px;">💾 Simpan Pengaturan</button>
      <div class="setting-info"><b>Info:</b> Setelah disimpan, halaman voting akan menampilkan countdown sesuai waktu di atas.</div>
      <div id="statusInfo" style="text-align:center; margin-top:16px;"></div>
    </div>
  </div>

    <div id="tabKandidat" class="hidden">

    <!-- Form Tambah Kandidat Baru -->
    <div class="setting-box" style="margin-bottom:20px;">
      <h3>➕ Tambah Kandidat Baru</h3>

      <div class="row-2">
        <div>
          <label>Nama Kandidat</label>
          <input type="text" id="addNama" placeholder="Contoh: Ahmad Rizky">
        </div>
        <div>
          <label>Kelas</label>
          <input type="text" id="addKelas" placeholder="XI IPA 1">
        </div>
      </div>

      <label>Visi (opsional)</label>
      <input type="text" id="addVisi" placeholder="OSIS Aktif & Kreatif">

      <div id="addKandidatMsg" style="margin-top:12px;"></div>

      <button class="btn" onclick="tambahKandidat()" style="margin-top:14px;">
        ➕ Tambah Kandidat
      </button>
    </div>

    <!-- Daftar Kandidat -->
    <h3 style="color:#1b5e20; font-size:15px; margin-bottom:14px;">📋 Daftar Kandidat</h3>
    <div id="kandidatList-admin"></div>
  </div>

  <div id="tabSiswa" class="hidden">
    <div class="setting-box" style="margin-bottom:20px;">
      <h3>➕ Tambah Siswa Baru</h3>
      <div class="row-2">
        <div>
          <label>NIS</label>
          <input type="text" id="newNis" placeholder="Contoh: 12348">
        </div>
        <div>
          <label>Kelas</label>
          <input type="text" id="newKelas" placeholder="X IPA 1">
        </div>
      </div>
      <label>Nama Lengkap</label>
      <input type="text" id="newNama" placeholder="Budi Setiawan">
      <div id="siswaMsg" style="margin-top:12px;"></div>
      <button class="btn" onclick="tambahSiswa()" style="margin-top:14px;">➕ Tambah Siswa</button>
    </div>

    <div class="stats-mini">
      <div class="stat-mini">
        <div class="val" id="totalSiswa">0</div>
        <div class="lbl">Total Siswa</div>
      </div>
      <div class="stat-mini">
        <div class="val" id="sudahVote">0</div>
        <div class="lbl">Sudah Memilih</div>
      </div>
      <div class="stat-mini">
        <div class="val" id="belumVote">0</div>
        <div class="lbl">Belum Memilih</div>
      </div>
    </div>

    <input type="text" id="searchSiswa" placeholder="🔍 Cari NIS / Nama / Kelas..." oninput="filterSiswa()" style="width:100%; padding:10px 14px; border:2px solid #c8e6c9; border-radius:8px; font-size:14px; outline:none; margin-bottom:14px; font-family:inherit; box-sizing:border-box;">

    <div class="history-scroll">
      <table class="history-table">
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

  <div id="tabPassword" class="hidden">
    <div class="setting-box" style="max-width:500px; margin:0 auto;">
      <h3>🔑 Ganti Password Admin</h3>
      <label>Password Lama</label>
      <input type="password" id="pwLama" placeholder="••••••••">
      <label>Password Baru</label>
      <input type="password" id="pwBaru" placeholder="Minimal 6 karakter">
      <label>Konfirmasi Password Baru</label>
      <input type="password" id="pwKonfirm" placeholder="Ulangi password baru">
      <div id="pwMsg" style="margin-top:14px;"></div>
      <button class="btn" onclick="gantiPassword()" style="margin-top:16px;">🔒 Simpan Password Baru</button>
      <div class="setting-info" style="margin-top:16px;"><b>Info:</b> Password minimal 6 karakter.</div>
    </div>
  </div>

  <div style="display:flex; gap:10px; margin-top:20px;">
    <a href="index.php" class="btn btn-outline" style="flex:1; text-align:center; text-decoration:none; margin:0;">← Halaman Voting</a>
    <button class="btn" onclick="logoutAdmin()" style="flex:1; background:#c62828;">🚪 Logout</button>
  </div>

  <p style="text-align:center; color:#66bb6a; font-size:12px; margin-top:12px;">
    Login sebagai: <b id="adminNama">-</b>
  </p>

  <p class="footer-text">© 2025 Panitia Pemilihan OSIS</p>
</div>

<script>
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

async function logoutAdmin() {
  if (!confirm('Yakin mau logout?')) return;
  try { await fetch(API + '?action=logout_admin'); } catch (e) {}
  window.location.href = 'login.php';
}

function switchTab(tab, el) {
  var tabs = document.querySelectorAll('.tab');
  for (var i = 0; i < tabs.length; i++) tabs[i].classList.remove('active');
  el.classList.add('active');

  document.getElementById('tabHasil').classList.toggle('hidden', tab !== 'hasil');
  document.getElementById('tabHistory').classList.toggle('hidden', tab !== 'history');
  document.getElementById('tabSetting').classList.toggle('hidden', tab !== 'setting');
  document.getElementById('tabKandidat').classList.toggle('hidden', tab !== 'kandidat');
  document.getElementById('tabSiswa').classList.toggle('hidden', tab !== 'siswa');
  document.getElementById('tabPassword').classList.toggle('hidden', tab !== 'password');

  if (tab === 'history') loadHistory();
  if (tab === 'setting') loadPengaturan();
  if (tab === 'kandidat') loadKandidatAdmin();
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
        '<h3>' + escapeHtml(k.nama) + '</h3>' +
        '<small>' + escapeHtml(k.kelas) + '</small>' +
        '<div class="bar"><div class="bar-fill" style="width:' + persen + '%"></div></div>' +
        '<div class="suara">' + k.suara + ' suara (' + persen + '%)</div></div>';
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
        '<td><b>' + escapeHtml(h.nis) + '</b></td>' +
        '<td>' + escapeHtml(h.nama_siswa) + '<br><small style="color:#66bb6a;">' + escapeHtml(h.kelas_siswa || '-') + '</small></td>' +
        '<td><b style="color:#2e7d32;">' + escapeHtml(h.nama_kandidat) + '</b><br><small style="color:#66bb6a;">' + escapeHtml(h.kelas_kandidat || '') + '</small></td>' +
        '<td><span class="badge-ip">' + escapeHtml(h.ip_address || '-') + '</span></td>' +
        '<td><span class="badge-time">' + formatTime(h.waktu) + '</span></td>' +
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
    var cls = 'status-aktif', txt = '🟢 Voting Sedang Berlangsung';
    if (data.status === 'belum_mulai') { cls = 'status-belum'; txt = '🟠 Voting Belum Dimulai'; }
    else if (data.status === 'selesai') { cls = 'status-selesai'; txt = '🔴 Voting Telah Ditutup'; }
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
      msg.innerHTML = '<div class="alert alert-success">✅ Pengaturan waktu tersimpan!</div>';
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
      '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">' +
      '<div>' +
      '<h3>Kandidat #' + (idx + 1) + ' — ' + escapeHtml(k.nama) + '</h3>' +
      '<small>' + escapeHtml(k.kelas) + '</small>' +
      '</div>' +
      '<button onclick="hapusKandidat(' + k.id + ', \'' + escapeHtml(k.nama).replace(/'/g, "\\'") + '\')" ' +
      'style="background:#c62828;color:#fff;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:11px;font-family:inherit;">' +
      '🗑️ Hapus' +
      '</button>' +
      '</div>' +

      '<div class="foto-row">' +
      '<div class="foto-preview" id="preview-' + k.id + '">' + fotoHtml + '</div>' +
      '<div style="flex:1;">' +
      '<input type="file" id="file-' + k.id + '" accept="image/*" onchange="uploadFoto(' + k.id + ')" style="padding:6px;font-size:12px;">' +
      '<small style="color:#66bb6a;font-size:11px;">JPG/PNG/WEBP, maks 2 MB</small>' +
      '</div></div>' +

      '<div class="form-group"><label>Nama</label><input type="text" id="nama-' + k.id + '" value="' + escapeHtml(k.nama) + '"></div>' +
      '<div class="row-2">' +
      '<div class="form-group"><label>Kelas</label><input type="text" id="kelas-' + k.id + '" value="' + escapeHtml(k.kelas) + '"></div>' +
      '<div class="form-group"><label>TTL</label><input type="text" id="ttl-' + k.id + '" value="' + escapeHtml(k.ttl || '') + '" placeholder="Jakarta, 1 Jan 2008"></div>' +
      '</div>' +
      '<div class="form-group"><label>Visi</label><input type="text" id="visi-' + k.id + '" value="' + escapeHtml(k.visi || '') + '"></div>' +
      '<div class="form-group"><label>Misi (satu per baris)</label><textarea id="misi-' + k.id + '">' + escapeHtml(k.misi || '') + '</textarea></div>' +
      '<div class="form-group"><label>Program Kerja (satu per baris)</label><textarea id="program-' + k.id + '">' + escapeHtml(k.program || '') + '</textarea></div>' +
      '<div class="form-group"><label>Quote / Slogan</label><input type="text" id="quote-' + k.id + '" value="' + escapeHtml(k.quote || '') + '"></div>' +
      '<div class="form-group"><label>Motivasi</label><textarea id="motivasi-' + k.id + '">' + escapeHtml(k.motivasi || '') + '</textarea></div>' +

      '<button class="btn" onclick="simpanProfil(' + k.id + ')" style="padding:10px;">💾 Simpan Profil</button>' +
      '<div id="msg-' + k.id + '" style="margin-top:8px;"></div>' +
      '</div>';
  }).join('');
}
async function tambahKandidat() {
  var nama  = document.getElementById('addNama').value.trim();
  var kelas = document.getElementById('addKelas').value.trim();
  var visi  = document.getElementById('addVisi').value.trim();
  var msg   = document.getElementById('addKandidatMsg');

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

    var res  = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '</div>';

      document.getElementById('addNama').value  = '';
      document.getElementById('addKelas').value = '';
      document.getElementById('addVisi').value  = '';
      document.getElementById('addNama').focus();

      // Refresh daftar kandidat
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
  if (!confirm('Yakin hapus kandidat:\n\n' + nama + '\n\nData yang sudah dihapus tidak bisa dikembalikan.')) {
    return;
  }

  try {
    var fd = new FormData();
    fd.append('action', 'hapus_kandidat');
    fd.append('id', id);

    var res  = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) {
      alert('✅ ' + data.message);
      loadKandidatAdmin();
    } else {
      alert('❌ ' + data.message);
    }
  } catch (e) {
    alert('Error: ' + e.message);
  }
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
      msg.innerHTML = '<div class="alert alert-success">✅ Tersimpan!</div>';
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
      msg.innerHTML = '<div class="alert alert-success">✅ Foto berhasil diupload!</div>';
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
      ? '<span class="badge-ip" style="background:#c8e6c9;color:#1b5e20;">✓ Sudah</span>'
      : '<span class="badge-time" style="background:#fff3e0;color:#e65100;">Belum</span>';
    return '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td><b>' + escapeHtml(s.nis) + '</b></td>' +
      '<td>' + escapeHtml(s.nama) + '</td>' +
      '<td>' + escapeHtml(s.kelas || '-') + '</td>' +
      '<td>' + badge + '</td>' +
      '<td>' +
      '<button onclick="editSiswa(\'' + escapeHtml(s.nis) + '\')" class="btn-action btn-edit">✏️ Edit</button>' +
      '<button onclick="hapusSiswa(\'' + escapeHtml(s.nis) + '\')" class="btn-action btn-hapus">🗑️ Hapus</button>' +
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
      msg.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '</div>';
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

    if (data.success) { alert('✅ ' + data.message); loadSiswa(); }
    else { alert('❌ ' + data.message); }
  } catch (e) { alert('Error: ' + e.message); }
}

async function hapusSiswa(nis) {
  var s = siswaList.find(function(x) { return x.nis === nis; });
  if (!s) return;

  if (s.sudah_memilih) {
    alert('❌ Siswa ini sudah memilih, tidak bisa dihapus.');
    return;
  }

  if (!confirm('Yakin hapus siswa:\n\nNIS: ' + s.nis + '\nNama: ' + s.nama)) return;

  try {
    var fd = new FormData();
    fd.append('action', 'hapus_siswa');
    fd.append('nis', nis);

    var res = await fetch(API, { method: 'POST', body: fd });
    var data = await res.json();

    if (data.success) { alert('✅ ' + data.message); loadSiswa(); }
    else { alert('❌ ' + data.message); }
  } catch (e) { alert('Error: ' + e.message); }
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
      msg.innerHTML = '<div class="alert alert-success">✅ Password berhasil diganti!</div>';
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