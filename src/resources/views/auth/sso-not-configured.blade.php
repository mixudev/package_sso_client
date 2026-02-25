<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SSO Not Configured — MixuAuth</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;1,9..144,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #F7F5F2;
    --surface: #FFFFFF;
    --border: #E8E4DF;
    --border-strong: #D4CFC8;
    --text-primary: #1A1714;
    --text-secondary: #6B6560;
    --text-muted: #9E9892;
    --accent: #C8472B;
    --accent-light: #FDF1EE;
    --accent-border: #F0C4BB;
    --code-bg: #F0EDE9;
    --code-text: #8B3A24;
  }

  html, body {
    min-height: 100vh;
    background: var(--bg);
    font-family: 'DM Sans', sans-serif;
    color: var(--text-primary);
    -webkit-font-smoothing: antialiased;
  }

  body {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }

  .container {
    width: 100%;
    max-width: 560px;
    animation: fadeUp 0.5s ease both;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Header */
  .brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 40px;
  }

  .brand-icon {
    width: 32px;
    height: 32px;
    background: var(--text-primary);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .brand-icon svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: white;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .brand-name {
    font-family: 'Fraunces', serif;
    font-size: 18px;
    font-weight: 400;
    letter-spacing: -0.01em;
    color: var(--text-primary);
  }

  /* Card */
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
  }

  .card-header {
    padding: 24px 28px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: flex-start;
    gap: 14px;
  }

  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--accent);
    margin-top: 7px;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px var(--accent-border);
    animation: pulse 2s ease infinite;
  }

  @keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 3px var(--accent-border); }
    50%       { box-shadow: 0 0 0 5px var(--accent-border); }
  }

  .card-header-content h1 {
    font-family: 'Fraunces', serif;
    font-size: 20px;
    font-weight: 300;
    letter-spacing: -0.02em;
    color: var(--text-primary);
    line-height: 1.3;
  }

  .card-header-content p {
    margin-top: 4px;
    font-size: 13.5px;
    color: var(--text-secondary);
    line-height: 1.5;
    font-weight: 300;
  }

  /* Body */
  .card-body {
    padding: 24px 28px;
  }

  .section-label {
    font-size: 10.5px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    margin-bottom: 12px;
  }

  .env-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .env-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    transition: background 0.15s;
  }

  .env-item:hover {
    background: var(--code-bg);
  }

  .env-item-key {
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    color: var(--code-text);
    font-weight: 500;
    white-space: nowrap;
    padding-top: 1px;
    min-width: 200px;
  }

  .env-item-desc {
    font-size: 12.5px;
    color: var(--text-secondary);
    font-weight: 300;
    line-height: 1.5;
  }

  .env-item-desc .tag {
    display: inline-block;
    font-size: 10px;
    font-family: 'DM Mono', monospace;
    color: var(--text-muted);
    background: var(--code-bg);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 1px 6px;
    margin-left: 4px;
    vertical-align: middle;
  }

  /* Divider */
  .divider {
    height: 1px;
    background: var(--border);
    margin: 20px 0;
  }

  /* Footer action */
  .card-footer {
    padding: 18px 28px;
    background: var(--code-bg);
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .footer-icon {
    flex-shrink: 0;
    color: var(--text-muted);
  }

  .footer-text {
    font-size: 12.5px;
    color: var(--text-secondary);
    font-weight: 300;
  }

  code {
    font-family: 'DM Mono', monospace;
    font-size: 11.5px;
    color: var(--code-text);
    background: var(--accent-light);
    border: 1px solid var(--accent-border);
    border-radius: 5px;
    padding: 2px 7px;
  }

  .footer-text code {
    color: var(--text-primary);
    background: var(--surface);
    border-color: var(--border-strong);
  }

  /* Docs link */
  .docs-link {
    margin-top: 24px;
    text-align: center;
  }

  .docs-link a {
    font-size: 12px;
    color: var(--text-muted);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: color 0.15s;
  }

  .docs-link a:hover {
    color: var(--text-secondary);
  }
</style>
</head>
<body>

<div class="container">

  <div class="brand">
    <div class="brand-icon">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    </div>
    <span class="brand-name">MixuAuth</span>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="status-dot"></div>
      <div class="card-header-content">
        <h1>SSO Authentication Not Configured</h1>
        <p>Tambahkan variabel berikut ke file <code>.env</code> untuk mengaktifkan SSO.</p>
      </div>
    </div>

    <div class="card-body">
      <p class="section-label">Required Environment Variables</p>
      <ul class="env-list">
        <li class="env-item">
          <span class="env-item-key">AUTH_BASE_URL</span>
          <span class="env-item-desc">URL SSO Server<span class="tag">e.g. https://auth.example.com</span></span>
        </li>
        <li class="env-item">
          <span class="env-item-key">AUTH_CLIENT_ID</span>
          <span class="env-item-desc">OAuth2 Client ID</span>
        </li>
        <li class="env-item">
          <span class="env-item-key">AUTH_CLIENT_SECRET</span>
          <span class="env-item-desc">OAuth2 Client Secret</span>
        </li>
        <li class="env-item">
          <span class="env-item-key">AUTH_REDIRECT_URI</span>
          <span class="env-item-desc">Redirect URI<span class="tag">default: APP_URL/auth/callback</span></span>
        </li>
        <li class="env-item">
          <span class="env-item-key">AUTH_SCOPES</span>
          <span class="env-item-desc">Requested Scopes<span class="tag">default: empty</span></span>
        </li>
        <li class="env-item">
          <span class="env-item-key">SSO_WEBHOOK_SECRET</span>
          <span class="env-item-desc">Secret untuk Global Logout webhook</span>
        </li>
      </ul>
    </div>

    <div class="card-footer">
      <svg class="footer-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="4 17 10 11 4 5"></polyline><line x1="12" y1="19" x2="20" y2="19"></line>
      </svg>
      <p class="footer-text">Setelah konfigurasi selesai, jalankan <code>php artisan migrate</code></p>
    </div>
  </div>

  <div class="docs-link">
    <a href="#">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Lihat dokumentasi lengkap
    </a>
  </div>

</div>

</body>
</html>