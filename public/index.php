<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-Voting OSIS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="voting-page">

<div class="voting-wrap">

  <!-- ============ HEADER ============ -->
  <header class="voting-header">
    <div class="voting-logo">E-VOTING OSIS</div>
    <div class="voting-sub">Pemilihan Ketua OSIS 2026</div>
  </header>

  <!-- ============ COUNTDOWN ============ -->
  <div id="countdownBox" class="countdown-box hidden">
    <div class="countdown-label">Waktu Tersisa</div>
    <div class="countdown-timer" id="countdownTimer">
      <div class="time-unit">
        <div class="time-val" id="cdDays">00</div>
        <div class="time-lbl">Hari</div>
      </div>
      <div class="time-unit">
        <div class="time-val" id="cdHours">00</div>
        <div class="time-lbl">Jam</div>
      </div>
      <div class="time-unit">
        <div class="time-val" id="cdMinutes">00</div>
        <div class="time-lbl">Menit</div>
      </div>
      <div class="time-unit">
        <div class="time-val" id="cdSeconds">00</div>
        <div class="time-lbl">Detik</div>
      </div>
    </div>
    <div class="countdown-info" id="countdownInfo"></div>
  </div>

  <!-- ============ STEP 1: LOGIN ============ -->
  <section id="stepLogin" class="voting-card">
    <h2 class="card-title">Login Pemilih</h2>

    <div id="loginAlert"></div>

    <div class="form-group">
      <label>NIS (Nomor Induk Siswa)</label>
      <input type="text" id="nis" placeholder="Contoh: 12345" autocomplete="off">
    </div>

    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" id="nama" placeholder="Masukkan nama lengkap" autocomplete="off">
    </div>

    <button class="btn-primary" id="btnLogin" onclick="login()">Masuk</button>
  </section>

  <!-- ============ STEP 2: VOTING ============ -->
  <section id="stepVoting" class="voting-card hidden">
    <div class="greeting">
      Halo, <strong id="userName"></strong>
    </div>
    <p class="instruction">Silakan pilih satu kandidat. Klik kartu untuk melihat profil lengkap.</p>

    <div id="candidateList"></div>

    <button class="btn-primary" id="btnVote" onclick="submitVote()" disabled>
      Kirim Suara
    </button>
  </section>

  <!-- ============ STEP 3: SELESAI ============ -->
  <section id="stepDone" class="voting-card hidden">
    <div class="success-box">
      <div class="success-circle">✓</div>
      <h2>Terima Kasih!</h2>
      <p>Suara Anda telah berhasil dicatat.</p>
    </div>
    <button class="btn-outline" onclick="logout()">Keluar</button>
  </section>

  <!-- ============ FOOTER ============ -->
  <footer class="voting-footer">
    © 2026 Panitia Pemilihan OSIS
  </footer>

</div>

<!-- ============ MODAL PROFIL KANDIDAT ============ -->
<div class="modal-overlay" id="modalProfil" onclick="closeModal(event)">
  <div class="modal-content" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="tutupModal()">×</button>

    <div class="modal-header-photo" id="modalFoto"></div>

    <div class="modal-body">
      <h2 id="modalNama">-</h2>
      <p class="modal-kelas" id="modalKelas">-</p>

      <div class="modal-ttl" id="modalTtl" style="display:none;"></div>
      <div class="modal-quote" id="modalQuote" style="display:none;"></div>

      <div class="modal-section">
        <h3>Visi</h3>
        <p id="modalVisi">-</p>
      </div>

      <div class="modal-section" id="sectionMisi" style="display:none;">
        <h3>Misi</h3>
        <p id="modalMisi">-</p>
      </div>

      <div class="modal-section" id="sectionProgram" style="display:none;">
        <h3>Program Kerja</h3>
        <p id="modalProgram">-</p>
      </div>

      <div class="modal-section" id="sectionMotivasi" style="display:none;">
        <h3>Motivasi</h3>
        <p id="modalMotivasi">-</p>
      </div>

      <div class="modal-actions">
        <button class="btn-outline" onclick="tutupModal()">Tutup</button>
        <button class="btn-primary" id="modalPilihBtn" onclick="pilihDariModal()">Pilih Kandidat</button>
      </div>
    </div>
  </div>
</div>

<script src="assets/app.js"></script>
</body>
</html>