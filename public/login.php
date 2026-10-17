<?php 
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - E-Voting OSIS</title>
<link rel="stylesheet" href="assets/style.css">
<style>
  .login-page {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    font-size: 14px;
    color: #212121;
    background: #f5f5f5;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    margin: 0;
    line-height: 1.5;
  }
  .login-page * {
    box-sizing: border-box;
  }
  .login-wrap {
    width: 100%;
    max-width: 420px;
  }
  .login-header {
    text-align: center;
    padding: 0 0 20px;
  }
  .login-badge {
    display: inline-block;
    background: #fff3e0;
    color: #e65100;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    margin-bottom: 14px;
  }
  .login-logo {
    width: 60px;
    height: 60px;
    background: #1b5e20;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    color: #ffffff;
    font-size: 26px;
  }
  .login-title {
    font-size: 18px;
    font-weight: 700;
    color: #1b5e20;
    margin: 0 0 4px;
  }
  .login-sub {
    font-size: 12px;
    color: #757575;
    margin: 0;
  }
  .login-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 24px;
  }
  .login-card .form-group {
    margin-bottom: 16px;
  }
  .login-card .form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #616161;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .login-card .form-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    font-size: 14px;
    color: #212121;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    font-family: inherit;
  }
  .login-card .form-group input:focus {
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
  }
  .login-btn {
    display: block;
    width: 100%;
    padding: 11px 18px;
    background: #2e7d32;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
  }
  .login-btn:hover {
    background: #1b5e20;
  }
  .login-btn:disabled {
    background: #a5d6a7;
    cursor: not-allowed;
  }
  .login-footer {
    text-align: center;
    margin-top: 20px;
  }
  .login-footer a {
    color: #2e7d32;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
  }
  .login-footer a:hover {
    text-decoration: underline;
  }
  .login-copyright {
    text-align: center;
    color: #9e9e9e;
    font-size: 11px;
    margin-top: 16px;
  }
</style>
</head>
<body class="login-page">

<div class="login-wrap">

  <div class="login-header">
    <div class="login-badge">KHUSUS ADMIN</div>
    <div class="login-logo">🔑</div>
    <h1 class="login-title">Login Panitia</h1>
    <p class="login-sub">Masukkan kredensial admin</p>
  </div>

  <div class="login-card">
    <div id="loginAlert"></div>

    <div class="form-group">
      <label>Username</label>
      <input type="text" id="username" placeholder="admin" autocomplete="username" autofocus>
    </div>

    <div class="form-group">
      <label>Password</label>
      <input type="password" id="password" placeholder="••••••••" autocomplete="current-password">
    </div>

    <button class="login-btn" id="btnLogin" onclick="loginAdmin()">Masuk</button>
  </div>

  <div class="login-footer">
    <a href="index.php">← Halaman Voting Siswa</a>
  </div>

  <p class="login-copyright">© 2026 Panitia Pemilihan OSIS</p>

</div>

<script>
  const API = '../api/handler.php';

  async function loginAdmin() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const btn      = document.getElementById('btnLogin');
    const alertEl  = document.getElementById('loginAlert');

    if (!username || !password) {
      alertEl.innerHTML = '<div class="alert alert-error">Username dan password wajib diisi</div>';
      setTimeout(() => alertEl.innerHTML = '', 3000);
      return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner"></span>Memeriksa...';

    try {
      const fd = new FormData();
      fd.append('action', 'login_admin');
      fd.append('username', username);
      fd.append('password', password);

      const res  = await fetch(API, { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        alertEl.innerHTML = '<div class="alert alert-success">Login berhasil. Mengalihkan...</div>';
        setTimeout(() => window.location.href = 'admin.php', 700);
      } else {
        alertEl.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
        btn.disabled = false;
        btn.textContent = 'Masuk';
        document.getElementById('password').value = '';
        document.getElementById('password').focus();
      }
    } catch (e) {
      alertEl.innerHTML = `<div class="alert alert-error">Error: ${e.message}</div>`;
      btn.disabled = false;
      btn.textContent = 'Masuk';
    }
  }

  document.getElementById('username').addEventListener('keypress', e => {
    if (e.key === 'Enter') document.getElementById('password').focus();
  });
  document.getElementById('password').addEventListener('keypress', e => {
    if (e.key === 'Enter') loginAdmin();
  });
</script>
</body>
</html>