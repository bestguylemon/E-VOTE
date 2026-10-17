// ============================================
// KONFIGURASI
// ============================================
const API = '../api/handler.php';

// ============================================
// STATE
// ============================================
let selectedId        = null;
let kandidatList      = [];
let countdownInterval = null;
let votingEndTime     = null;

// ============================================
// DEVICE FINGERPRINT
// ============================================
function getDeviceId() {
  try {
    var data = [
      navigator.userAgent,
      navigator.language,
      screen.width + 'x' + screen.height,
      screen.colorDepth,
      new Date().getTimezoneOffset(),
      navigator.hardwareConcurrency || 'unknown',
      navigator.platform || 'unknown'
    ].join('|');

    var hash = 5381;
    for (var i = 0; i < data.length; i++) {
      hash = ((hash << 5) + hash) + data.charCodeAt(i);
      hash = hash & hash;
    }

    return 'dev_' + Math.abs(hash).toString(36);
  } catch (e) {
    return 'unknown';
  }
}

function getScreenRes() {
  return screen.width + 'x' + screen.height;
}

function getTimezone() {
  try {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
  } catch (e) {
    return 'unknown';
  }
}

// ============================================
// COUNTDOWN TIMER
// ============================================
async function initCountdown() {
  const box = document.getElementById('countdownBox');
  if (!box) return;

  try {
    const res  = await fetch(`${API}?action=pengaturan`);
    const data = await res.json();
    if (!data.success) return;

    if (data.status === 'belum_mulai') {
      box.classList.remove('hidden', 'warning', 'danger', 'ended');
      box.querySelector('.countdown-label').textContent = '⏳ Voting Belum Dimulai';
      votingEndTime = new Date(data.mulai);
      document.getElementById('countdownInfo').textContent =
        'Mulai: ' + formatDateTime(data.mulai);

    } else if (data.status === 'selesai') {
      showEndedState(box);
      disableVoting();

    } else {
      box.classList.remove('hidden', 'warning', 'danger', 'ended');
      votingEndTime = new Date(data.selesai);
      document.getElementById('countdownInfo').textContent =
        'Berakhir: ' + formatDateTime(data.selesai);
    }

    updateCountdown();
    if (countdownInterval) clearInterval(countdownInterval);
    countdownInterval = setInterval(updateCountdown, 1000);

  } catch (e) {
    console.error('Countdown error:', e);
  }
}

function updateCountdown() {
  if (!votingEndTime) return;

  const box  = document.getElementById('countdownBox');
  const now  = new Date();
  const diff = votingEndTime - now;

  if (diff <= 0) {
    showEndedState(box);
    clearInterval(countdownInterval);
    disableVoting();
    return;
  }

  const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  document.getElementById('cdDays').textContent    = pad(days);
  document.getElementById('cdHours').textContent   = pad(hours);
  document.getElementById('cdMinutes').textContent = pad(minutes);
  document.getElementById('cdSeconds').textContent = pad(seconds);

  box.classList.remove('warning', 'danger');
  const totalMin = diff / (1000 * 60);
  if (totalMin <= 5) {
    box.classList.add('danger');
  } else if (totalMin <= 30) {
    box.classList.add('warning');
  }
}

function showEndedState(box) {
  if (!box) return;
  box.classList.remove('hidden', 'warning', 'danger');
  box.classList.add('ended');
  box.querySelector('.countdown-label').textContent = '🔒 Voting Ditutup';
  box.querySelector('.countdown-timer').innerHTML = `
    <div class="countdown-ended-text">WAKTU VOTING TELAH BERAKHIR</div>
  `;
  const info = document.getElementById('countdownInfo');
  if (info) info.textContent = 'Terima kasih atas partisipasi Anda';
}

function disableVoting() {
  const btn = document.getElementById('btnVote');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Voting Ditutup';
  }
  document.querySelectorAll('.candidate').forEach(el => {
    el.style.pointerEvents = 'none';
    el.style.opacity = '0.6';
  });
}

function pad(n) {
  return String(n).padStart(2, '0');
}

function formatDateTime(str) {
  if (!str) return '-';
  const d = new Date(str);
  return d.toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
}

// ============================================
// VOTING FUNCTIONS
// ============================================
function switchStep(fromId, toId) {
  document.getElementById(fromId).classList.add('hidden');
  document.getElementById(toId).classList.remove('hidden');
}

function showAlert(elId, msg, type) {
  const el = document.getElementById(elId);
  el.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
  setTimeout(() => el.innerHTML = '', 3500);
}

function renderKandidat() {
  const list = document.getElementById('candidateList');
  list.innerHTML = kandidatList.map(k => {
    const avatarHtml = k.foto
      ? `<div class="avatar" style="padding:0;overflow:hidden;"><img src="${escapeHtml(k.foto)}" style="width:100%;height:100%;object-fit:cover;"></div>`
      : `<div class="avatar">${escapeHtml(k.nama.charAt(0))}</div>`;

    return `
      <div class="candidate" data-id="${k.id}" onclick="pilihKandidat(${k.id})">
        ${avatarHtml}
        <div class="candidate-info">
          <h3>${escapeHtml(k.nama)}</h3>
          <p>${escapeHtml(k.kelas)} • ${escapeHtml(k.visi || '')}</p>
          <small style="color:#2e7d32;font-size:11px;">👆 Klik untuk lihat profil</small>
        </div>
        <div class="check">✓</div>
      </div>
    `;
  }).join('');
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, s => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;',
    '"': '&quot;', "'": '&#39;'
  }[s]));
}

let currentModalId = null;

function pilihKandidat(id) {
  bukaModal(id);
}

async function bukaModal(id) {
  const box = document.getElementById('countdownBox');
  if (box && box.classList.contains('ended')) return;

  currentModalId = id;

  try {
    const res  = await fetch(`${API}?action=detail_kandidat&id=${id}`);
    const data = await res.json();

    if (!data.success) {
      alert(data.message);
      return;
    }

    const k = data.kandidat;

    const fotoEl = document.getElementById('modalFoto');
    if (k.foto) {
      fotoEl.innerHTML = `<img src="${escapeHtml(k.foto)}" alt="${escapeHtml(k.nama)}">`;
    } else {
      fotoEl.innerHTML = `<div class="avatar-big">${escapeHtml(k.nama.charAt(0))}</div>`;
    }

    document.getElementById('modalNama').textContent  = k.nama;
    document.getElementById('modalKelas').textContent = k.kelas;
    document.getElementById('modalVisi').textContent  = k.visi || '-';

    const ttlEl = document.getElementById('modalTtl');
    if (k.ttl) {
      ttlEl.textContent = '📍 ' + k.ttl;
      ttlEl.style.display = 'block';
    } else {
      ttlEl.style.display = 'none';
    }

    const quoteEl = document.getElementById('modalQuote');
    if (k.quote) {
      quoteEl.textContent = '"' + k.quote + '"';
      quoteEl.style.display = 'block';
    } else {
      quoteEl.style.display = 'none';
    }

    const misiSec = document.getElementById('sectionMisi');
    if (k.misi) {
      document.getElementById('modalMisi').textContent = k.misi;
      misiSec.style.display = 'block';
    } else {
      misiSec.style.display = 'none';
    }

    const progSec = document.getElementById('sectionProgram');
    if (k.program) {
      document.getElementById('modalProgram').textContent = k.program;
      progSec.style.display = 'block';
    } else {
      progSec.style.display = 'none';
    }

    const motSec = document.getElementById('sectionMotivasi');
    if (k.motivasi) {
      document.getElementById('modalMotivasi').textContent = k.motivasi;
      motSec.style.display = 'block';
    } else {
      motSec.style.display = 'none';
    }

    const btnPilih = document.getElementById('modalPilihBtn');
    const sudahDipilih = (selectedId === id);
    btnPilih.textContent = sudahDipilih ? '✓ Sudah Dipilih' : 'Pilih Kandidat';
    btnPilih.disabled = sudahDipilih;

    document.getElementById('modalProfil').classList.add('active');

  } catch (e) {
    console.error(e);
    alert('Gagal memuat profil: ' + e.message);
  }
}

function closeModal(e) {
  if (e.target.id === 'modalProfil') {
    tutupModal();
  }
}

function tutupModal() {
  document.getElementById('modalProfil').classList.remove('active');
}

function pilihDariModal() {
  if (!currentModalId) return;

  selectedId = currentModalId;

  document.querySelectorAll('.candidate').forEach(el => {
    el.classList.toggle('selected', parseInt(el.dataset.id) === selectedId);
  });

  document.getElementById('btnVote').disabled = false;
  tutupModal();
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') tutupModal();
});

async function login() {
  const nis  = document.getElementById('nis').value.trim();
  const nama = document.getElementById('nama').value.trim();
  const btn  = document.getElementById('btnLogin');

  if (!nis || !nama) {
    showAlert('loginAlert', 'NIS dan Nama wajib diisi', 'error');
    return;
  }

  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner"></span>Memeriksa...';

  try {
    const fd = new FormData();
    fd.append('action', 'login');
    fd.append('nis', nis);
    fd.append('nama', nama);

    const res  = await fetch(API, { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.success) {
      showAlert('loginAlert', data.message, 'error');
      btn.disabled = false;
      btn.textContent = 'Masuk';
      return;
    }

    document.getElementById('userName').textContent = data.nama;
    kandidatList = data.kandidat;
    switchStep('stepLogin', 'stepVoting');
    renderKandidat();

  } catch (e) {
    showAlert('loginAlert', 'Koneksi gagal: ' + e.message, 'error');
    btn.disabled = false;
    btn.textContent = 'Masuk';
  }
}

async function submitVote() {
  if (!selectedId) return;
  const btn = document.getElementById('btnVote');
  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner"></span>Mengirim...';

  try {
    const fd = new FormData();
    fd.append('action', 'vote');
    fd.append('kandidat_id', selectedId);
    fd.append('device_id', getDeviceId());
    fd.append('screen_res', getScreenRes());
    fd.append('timezone', getTimezone());
    fd.append('language', navigator.language || '');

    const res  = await fetch(API, { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      switchStep('stepVoting', 'stepDone');
    } else {
      showAlert('loginAlert', data.message, 'error');
      btn.disabled = false;
      btn.textContent = 'Kirim Suara';
    }
  } catch (e) {
    alert('Koneksi gagal: ' + e.message);
    btn.disabled = false;
    btn.textContent = 'Kirim Suara';
  }
}

async function logout() {
  await fetch(`${API}?action=logout`);
  location.reload();
}

// ============================================
// INIT
// ============================================
window.addEventListener('DOMContentLoaded', async () => {
  initCountdown();

  const el = document.getElementById('userName');
  if (!el) return;

  try {
    const res  = await fetch(`${API}?action=session`);
    const data = await res.json();

    if (data.success) {
      el.textContent = data.nama;
      kandidatList   = data.kandidat;
      switchStep('stepLogin', 'stepVoting');
      renderKandidat();
    }
  } catch (e) {
    console.error('Session error:', e);
  }

  const namaInput = document.getElementById('nama');
  if (namaInput) {
    namaInput.addEventListener('keypress', e => {
      if (e.key === 'Enter') login();
    });
  }
});