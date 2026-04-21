<?php
// PlumePDF — page principale
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PlumePDF — léger comme une plume</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,400&family=DM+Sans:wght@300;400;500&family=Caveat:wght@400;600&display=swap">
<style>
  :root {
    --sky-dawn: #fef3e8;
    --sky-morning: #fde4d4;
    --sky-noon: #e9ecf5;
    --sky-dusk: #d8d4e8;
    --sky-deep: #8a96b8;
    --cloud: #ffffff;
    --feather-ink: #3a4454;
    --feather-soft: #6b7590;
    --ember: #d67b5a;
    --ember-dark: #b85c3e;
    --mist: rgba(255, 255, 255, 0.6);
    --shadow-soft: 0 10px 40px -12px rgba(74, 88, 120, 0.15);
    --shadow-lift: 0 24px 60px -20px rgba(74, 88, 120, 0.28);
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  html, body {
    min-height: 100vh;
    font-family: 'DM Sans', sans-serif;
    color: var(--feather-ink);
    overflow-x: hidden;
  }

  body {
    background:
      radial-gradient(ellipse 80% 50% at 20% 0%, var(--sky-morning) 0%, transparent 60%),
      radial-gradient(ellipse 60% 40% at 80% 10%, var(--sky-dawn) 0%, transparent 55%),
      radial-gradient(ellipse 70% 60% at 50% 100%, var(--sky-dusk) 0%, transparent 65%),
      linear-gradient(180deg, var(--sky-noon) 0%, var(--sky-dusk) 100%);
    background-attachment: fixed;
    min-height: 100vh;
    position: relative;
  }

  /* Nuages flottants en fond */
  .cloud {
    position: fixed;
    background: radial-gradient(ellipse at center, var(--cloud) 0%, transparent 70%);
    border-radius: 50%;
    opacity: 0.55;
    pointer-events: none;
    z-index: 0;
    filter: blur(2px);
  }
  .cloud-1 { width: 380px; height: 140px; top: 8%; left: -5%; animation: drift-right 80s linear infinite; }
  .cloud-2 { width: 260px; height: 100px; top: 25%; right: -3%; animation: drift-left 95s linear infinite; }
  .cloud-3 { width: 320px; height: 120px; top: 60%; left: 10%; animation: drift-right 110s linear infinite; opacity: 0.4; }
  .cloud-4 { width: 200px; height: 80px; bottom: 15%; right: 15%; animation: drift-left 70s linear infinite; opacity: 0.5; }

  @keyframes drift-right {
    0% { transform: translateX(-100px); }
    100% { transform: translateX(calc(100vw + 200px)); }
  }
  @keyframes drift-left {
    0% { transform: translateX(100px); }
    100% { transform: translateX(calc(-100vw - 200px)); }
  }

  /* Plumes qui tombent doucement */
  .feather {
    position: fixed;
    pointer-events: none;
    z-index: 1;
    opacity: 0;
    animation: fall linear infinite;
  }
  @keyframes fall {
    0% { transform: translateY(-50px) rotate(0deg) translateX(0); opacity: 0; }
    10% { opacity: 0.6; }
    50% { transform: translateY(50vh) rotate(180deg) translateX(30px); }
    90% { opacity: 0.4; }
    100% { transform: translateY(105vh) rotate(360deg) translateX(-20px); opacity: 0; }
  }

  /* Oiseaux qui traversent */
  .birds {
    position: fixed;
    top: 15%;
    left: -80px;
    z-index: 1;
    opacity: 0.4;
    animation: fly-across 45s linear infinite;
    pointer-events: none;
  }
  @keyframes fly-across {
    0% { transform: translateX(0) translateY(0); }
    50% { transform: translateX(50vw) translateY(-30px); }
    100% { transform: translateX(calc(100vw + 100px)) translateY(0); }
  }

  main {
    position: relative;
    z-index: 10;
    max-width: 920px;
    margin: 0 auto;
    padding: 60px 32px 80px;
  }

  header {
    text-align: center;
    margin-bottom: 48px;
  }

  .logo {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
  }
  .logo svg { width: 38px; height: 38px; }

  h1 {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 300;
    font-size: clamp(2.8rem, 6vw, 4.2rem);
    letter-spacing: -0.01em;
    line-height: 1;
    color: var(--feather-ink);
  }
  h1 em {
    font-style: italic;
    font-weight: 400;
    color: var(--ember);
  }

  .tagline {
    font-family: 'Caveat', cursive;
    font-size: 1.4rem;
    color: var(--feather-soft);
    margin-top: 14px;
    letter-spacing: 0.02em;
  }

  /* Zone de drop */
  .dropzone {
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 2px dashed rgba(138, 150, 184, 0.4);
    border-radius: 32px;
    padding: 70px 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
  }
  .dropzone::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(214, 123, 90, 0.08) 0%, transparent 60%);
    opacity: 0;
    transition: opacity 0.4s;
    pointer-events: none;
  }
  .dropzone:hover, .dropzone.dragging {
    border-color: var(--ember);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lift);
    background: rgba(255, 255, 255, 0.75);
  }
  .dropzone:hover::before, .dropzone.dragging::before { opacity: 1; }

  .dropzone-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: float-icon 4s ease-in-out infinite;
  }
  @keyframes float-icon {
    0%, 100% { transform: translateY(0) rotate(-3deg); }
    50% { transform: translateY(-8px) rotate(3deg); }
  }

  .dropzone h2 {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 400;
    font-size: 1.8rem;
    margin-bottom: 8px;
  }
  .dropzone p {
    color: var(--feather-soft);
    font-size: 0.95rem;
    margin-bottom: 4px;
  }
  .dropzone .formats {
    font-family: 'Caveat', cursive;
    font-size: 1.15rem;
    color: var(--ember);
    margin-top: 12px;
  }

  input[type="file"] { display: none; }

  /* Liste des fichiers */
  .file-list {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .file-item {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 4px 16px -6px rgba(74, 88, 120, 0.12);
    animation: slide-in 0.4s cubic-bezier(0.4, 0, 0.2, 1) both;
  }
  @keyframes slide-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .file-thumb {
    width: 44px; height: 44px;
    border-radius: 10px;
    background: var(--sky-morning);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
  }
  .file-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .file-thumb svg { width: 22px; height: 22px; color: var(--ember-dark); }
  .file-info { flex: 1; min-width: 0; }
  .file-name {
    font-weight: 500;
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .file-size { font-size: 0.82rem; color: var(--feather-soft); }
  .file-remove {
    background: transparent;
    border: none;
    color: var(--feather-soft);
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s;
  }
  .file-remove:hover { background: rgba(214, 123, 90, 0.1); color: var(--ember-dark); }

  /* Bouton action */
  .actions {
    margin-top: 32px;
    display: flex;
    justify-content: center;
  }
  .btn-primary {
    background: linear-gradient(135deg, var(--ember) 0%, var(--ember-dark) 100%);
    color: white;
    border: none;
    padding: 16px 44px;
    border-radius: 100px;
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    font-weight: 500;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 10px 30px -10px rgba(184, 92, 62, 0.5);
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }
  .btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 15px 40px -10px rgba(184, 92, 62, 0.6);
  }
  .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }

  /* Modal de choix de compression */
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(138, 150, 184, 0.4);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 100;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fade-in 0.3s ease-out;
  }
  .modal-backdrop.active { display: flex; }
  @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }

  .modal {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(30px);
    border-radius: 28px;
    padding: 44px 40px;
    max-width: 520px;
    width: 100%;
    box-shadow: 0 40px 80px -20px rgba(74, 88, 120, 0.4);
    animation: slide-up 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }
  @keyframes slide-up {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  .modal h3 {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 400;
    font-size: 2rem;
    text-align: center;
    margin-bottom: 8px;
  }
  .modal .sub {
    text-align: center;
    color: var(--feather-soft);
    font-family: 'Caveat', cursive;
    font-size: 1.2rem;
    margin-bottom: 28px;
  }

  .compression-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
  .comp-option {
    background: rgba(253, 228, 212, 0.3);
    border: 2px solid transparent;
    border-radius: 20px;
    padding: 24px 20px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    font-family: inherit;
    color: inherit;
  }
  .comp-option:hover {
    border-color: var(--ember);
    background: rgba(253, 228, 212, 0.6);
    transform: translateY(-3px);
  }
  .comp-option .icon { font-size: 2rem; margin-bottom: 10px; }
  .comp-option h4 {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 1.3rem;
    margin-bottom: 4px;
  }
  .comp-option p {
    font-size: 0.85rem;
    color: var(--feather-soft);
    line-height: 1.4;
  }

  .modal-close {
    position: absolute;
    top: 16px; right: 16px;
    background: transparent;
    border: none;
    width: 36px; height: 36px;
    border-radius: 50%;
    cursor: pointer;
    color: var(--feather-soft);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .modal-close:hover { background: rgba(138, 150, 184, 0.15); }

  /* État de traitement */
  .processing {
    text-align: center;
    padding: 20px 0;
  }
  .processing .spinner {
    width: 60px; height: 60px;
    margin: 0 auto 20px;
    animation: spin 2s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .processing p {
    font-family: 'Caveat', cursive;
    font-size: 1.3rem;
    color: var(--feather-soft);
  }

  /* Messages erreur / succès */
  .toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: var(--feather-ink);
    color: white;
    padding: 14px 24px;
    border-radius: 100px;
    font-size: 0.92rem;
    box-shadow: var(--shadow-lift);
    z-index: 200;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: 90%;
  }
  .toast.show { transform: translateX(-50%) translateY(0); }
  .toast.error { background: var(--ember-dark); }
  .toast.success { background: #5a8a6b; }

  footer {
    text-align: center;
    margin-top: 60px;
    font-family: 'Caveat', cursive;
    color: var(--feather-soft);
    font-size: 1.1rem;
  }
  .footer-links {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 28px;
    margin-top: 14px;
    flex-wrap: wrap;
  }
  .footer-links a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.78rem;
    color: var(--feather-soft);
    text-decoration: none;
    opacity: 0.75;
    transition: opacity 0.2s, color 0.2s;
  }
  .footer-links a:hover { opacity: 1; color: var(--ember); }
  .footer-links a svg { flex-shrink: 0; }

  .privacy-note {
    margin-top: 18px;
    text-align: center;
    font-size: 0.82rem;
    color: var(--feather-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    opacity: 0.85;
  }
  .privacy-note svg {
    flex-shrink: 0;
    color: var(--sky-deep);
  }

  @media (max-width: 600px) {
    .compression-options { grid-template-columns: 1fr; }
    main { padding: 40px 20px 60px; }
    .dropzone { padding: 50px 24px; }
  }
</style>
</head>
<body>

<!-- Nuages -->
<div class="cloud cloud-1"></div>
<div class="cloud cloud-2"></div>
<div class="cloud cloud-3"></div>
<div class="cloud cloud-4"></div>

<!-- Oiseaux -->
<svg class="birds" width="80" height="30" viewBox="0 0 80 30" xmlns="http://www.w3.org/2000/svg">
  <path d="M5 15 Q10 8, 15 15 Q20 8, 25 15" stroke="#3a4454" stroke-width="1.5" fill="none" stroke-linecap="round"/>
  <path d="M35 10 Q40 3, 45 10 Q50 3, 55 10" stroke="#3a4454" stroke-width="1.2" fill="none" stroke-linecap="round"/>
  <path d="M60 18 Q64 13, 68 18 Q72 13, 76 18" stroke="#3a4454" stroke-width="1" fill="none" stroke-linecap="round"/>
</svg>

<main>
  <header>
    <div class="logo">
      <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
        <path d="M8 32 C8 20, 18 8, 32 8 C30 18, 24 26, 16 30 L14 32 Z"
              fill="none" stroke="#d67b5a" stroke-width="1.5" stroke-linejoin="round"/>
        <path d="M14 32 L20 26 M14 32 L18 22 M14 32 L16 18"
              stroke="#d67b5a" stroke-width="1" stroke-linecap="round" opacity="0.6"/>
      </svg>
    </div>
    <h1>Plume<em>PDF</em></h1>
    <p class="tagline">~ des PDF légers, portés par le vent ~</p>
  </header>

  <div class="dropzone" id="dropzone">
    <div class="dropzone-icon">
      <svg width="72" height="72" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="featherGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#d67b5a"/>
            <stop offset="100%" stop-color="#b85c3e"/>
          </linearGradient>
        </defs>
        <path d="M14 58 C14 38, 30 16, 54 14 C52 34, 42 50, 26 56 L22 58 Z"
              fill="url(#featherGrad)" opacity="0.85"/>
        <path d="M22 58 L34 46 M22 58 L30 40 M22 58 L26 34 M22 58 L24 28"
              stroke="#fff" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
      </svg>
    </div>
    <h2>Déposez vos fichiers ici</h2>
    <p>ou cliquez pour parcourir</p>
    <p class="formats">images · PDF · HEIC — tous bienvenus</p>
    <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,.heic,.heif,.pdf,image/*,application/pdf">
  </div>

  <div class="file-list" id="fileList"></div>

  <div class="actions">
    <button class="btn-primary" id="generateBtn" disabled>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2 L12 16 M5 9 L12 2 L19 9" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M4 20 L20 20" stroke-linecap="round"/>
      </svg>
      Générer le PDF
    </button>
  </div>

  <p class="privacy-note">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
    Aucun fichier n'est conservé sur le serveur — pensez à sauvegarder votre PDF téléchargé, il ne pourra pas être récupéré.
  </p>

  <footer>
    léger comme une plume, silencieux comme un vol d'oiseau
    <div class="footer-links">
      <a href="https://www.gnu.org/licenses/gpl-3.0.fr.html" target="_blank" rel="noopener">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        Licence GNU GPL v3
      </a>
      <span style="opacity:0.3; font-family:'DM Sans',sans-serif; font-size:0.75rem;">·</span>
      <a href="https://github.com/sbois/PlumePDF/tree/main" target="_blank" rel="noopener">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.868-.013-1.703-2.782.604-3.369-1.342-3.369-1.342-.454-1.154-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0 1 12 6.836a9.59 9.59 0 0 1 2.504.337c1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
        </svg>
        GitHub — sbois/PlumePDF
      </a>
    </div>
  </footer>
</main>

<!-- Modal choix compression -->
<div class="modal-backdrop" id="modal">
  <div class="modal" id="modalContent">
    <button class="modal-close" id="modalClose" aria-label="Fermer">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M6 6 L18 18 M18 6 L6 18" stroke-linecap="round"/>
      </svg>
    </button>
    <div id="modalBody">
      <h3>Quelle légèreté ?</h3>
      <p class="sub">~ choisissez le poids des plumes ~</p>
      <div class="compression-options">
        <button class="comp-option" data-level="medium">
          <div class="icon">🪶</div>
          <h4>Compression moyenne</h4>
          <p>Qualité préservée, fichier raisonnable</p>
        </button>
        <button class="comp-option" data-level="high">
          <div class="icon">🕊️</div>
          <h4>Compression élevée</h4>
          <p>Le plus léger, idéal pour l'envoi</p>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
  const dropzone = document.getElementById('dropzone');
  const fileInput = document.getElementById('fileInput');
  const fileList = document.getElementById('fileList');
  const generateBtn = document.getElementById('generateBtn');
  const modal = document.getElementById('modal');
  const modalClose = document.getElementById('modalClose');
  const modalBody = document.getElementById('modalBody');
  const toast = document.getElementById('toast');

  let files = [];
  const ACCEPTED = ['image/jpeg','image/jpg','image/png','image/heic','image/heif','application/pdf'];
  const ACCEPTED_EXT = ['jpg','jpeg','png','heic','heif','pdf'];

  // Plumes qui tombent
  function spawnFeathers() {
    const feathers = ['🪶'];
    for (let i = 0; i < 6; i++) {
      const f = document.createElement('div');
      f.className = 'feather';
      f.textContent = feathers[0];
      f.style.left = Math.random() * 100 + '%';
      f.style.fontSize = (14 + Math.random() * 14) + 'px';
      f.style.animationDuration = (15 + Math.random() * 20) + 's';
      f.style.animationDelay = (Math.random() * 20) + 's';
      document.body.appendChild(f);
    }
  }
  spawnFeathers();

  function showToast(msg, type = '') {
    toast.textContent = msg;
    toast.className = 'toast show ' + type;
    setTimeout(() => toast.classList.remove('show'), 3500);
  }

  function isAccepted(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    return ACCEPTED.includes(file.type) || ACCEPTED_EXT.includes(ext);
  }

  function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko';
    return (bytes / (1024 * 1024)).toFixed(2) + ' Mo';
  }

  function renderFiles() {
    fileList.innerHTML = '';
    files.forEach((file, idx) => {
      const item = document.createElement('div');
      item.className = 'file-item';

      const thumb = document.createElement('div');
      thumb.className = 'file-thumb';
      const isImage = file.type.startsWith('image/') && !file.name.toLowerCase().match(/\.(heic|heif)$/);
      if (isImage) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        thumb.appendChild(img);
      } else {
        thumb.innerHTML = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')
          ? '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>'
          : '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>';
      }
      item.appendChild(thumb);

      const info = document.createElement('div');
      info.className = 'file-info';
      info.innerHTML = `<div class="file-name">${file.name}</div><div class="file-size">${formatSize(file.size)}</div>`;
      item.appendChild(info);

      const rm = document.createElement('button');
      rm.className = 'file-remove';
      rm.setAttribute('aria-label', 'Retirer');
      rm.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6 L18 18 M18 6 L6 18" stroke-linecap="round"/></svg>';
      rm.onclick = () => { files.splice(idx, 1); renderFiles(); };
      item.appendChild(rm);

      fileList.appendChild(item);
    });
    generateBtn.disabled = files.length === 0;
  }

  function addFiles(newFiles) {
    const accepted = [];
    const rejected = [];
    for (const f of newFiles) {
      if (isAccepted(f)) accepted.push(f); else rejected.push(f.name);
    }
    // vérif cohérence : éviter mélange PDF multipages + images ? Non, on gère tout.
    files = files.concat(accepted);
    renderFiles();
    if (rejected.length) showToast('Format non supporté : ' + rejected.join(', '), 'error');
  }

  // Drag events
  ['dragenter', 'dragover'].forEach(ev => {
    dropzone.addEventListener(ev, e => {
      e.preventDefault(); e.stopPropagation();
      dropzone.classList.add('dragging');
    });
  });
  ['dragleave', 'drop'].forEach(ev => {
    dropzone.addEventListener(ev, e => {
      e.preventDefault(); e.stopPropagation();
      if (ev === 'dragleave' && e.target !== dropzone) return;
      dropzone.classList.remove('dragging');
    });
  });
  dropzone.addEventListener('drop', e => {
    addFiles(e.dataTransfer.files);
  });
  dropzone.addEventListener('click', () => fileInput.click());
  fileInput.addEventListener('change', e => {
    addFiles(e.target.files);
    fileInput.value = '';
  });

  // Modal
  function openModal() {
    modalBody.innerHTML = `
      <h3>Quelle légèreté ?</h3>
      <p class="sub">~ choisissez le poids des plumes ~</p>
      <div class="compression-options">
        <button class="comp-option" data-level="medium">
          <div class="icon">🪶</div>
          <h4>Compression moyenne</h4>
          <p>Qualité préservée, fichier raisonnable</p>
        </button>
        <button class="comp-option" data-level="high">
          <div class="icon">🕊️</div>
          <h4>Compression élevée</h4>
          <p>Le plus léger, idéal pour l'envoi</p>
        </button>
      </div>
    `;
    modal.classList.add('active');
    modalBody.querySelectorAll('.comp-option').forEach(btn => {
      btn.onclick = () => submit(btn.dataset.level);
    });
  }
  function closeModal() { modal.classList.remove('active'); }
  modalClose.addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

  generateBtn.addEventListener('click', () => {
    if (files.length === 0) return;
    openModal();
  });

  async function submit(level) {
    modalBody.innerHTML = `
      <div class="processing">
        <svg class="spinner" viewBox="0 0 50 50">
          <circle cx="25" cy="25" r="20" fill="none" stroke="#d67b5a" stroke-width="3"
                  stroke-linecap="round" stroke-dasharray="90 150" opacity="0.8"/>
        </svg>
        <p>~ assemblage des plumes ~</p>
      </div>
    `;

    const fd = new FormData();
    fd.append('compression', level);
    files.forEach(f => fd.append('files[]', f));

    try {
      const res = await fetch('process.php', { method: 'POST', body: fd });

      if (!res.ok) {
        const txt = await res.text();
        throw new Error(txt || 'Erreur serveur');
      }

      const contentType = res.headers.get('content-type') || '';
      if (!contentType.includes('application/pdf')) {
        const txt = await res.text();
        throw new Error(txt || 'Le serveur n\'a pas renvoyé de PDF');
      }

      const blob = await res.blob();
      const disposition = res.headers.get('content-disposition') || '';
      const match = disposition.match(/filename="?([^"]+)"?/);
      const fname = match ? match[1] : 'plumepdf.pdf';

      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = fname;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);

      closeModal();
      showToast('PDF téléchargé — ' + formatSize(blob.size), 'success');

      // Reset
      files = [];
      renderFiles();
    } catch (err) {
      modalBody.innerHTML = `
        <h3>Un souffle contraire…</h3>
        <p class="sub">~ une erreur s'est glissée ~</p>
        <p style="text-align:center; color: var(--feather-soft); padding: 20px 0; font-family: 'DM Sans', sans-serif;">${err.message || err}</p>
        <div style="text-align:center; margin-top: 20px;">
          <button class="btn-primary" onclick="document.getElementById('modal').classList.remove('active')">Fermer</button>
        </div>
      `;
    }
  }
</script>

</body>
</html>
