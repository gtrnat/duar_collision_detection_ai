<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Response Console — Live Dispatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root{
      --bg: #0b0b0b;
      --surface: #141414;
      --surface-2: #1a1a1a;
      --surface-3: #202020;
      --border: #2a2a2a;
      --border-soft: #1f1f1f;
      --text: #f4f4f2;
      --text-dim: #a3a29e;
      --text-faint: #6b6a66;
      --white: #ffffff;
      --green: #4ADE80;
      --green-dim: #244a34;
      --red: #FF5B54;
      --red-dim: #4a201d;
      --font-display: 'Space Grotesk', sans-serif;
      --font-mono: 'IBM Plex Mono', monospace;
      --radius-lg: 26px;
      --radius-md: 16px;
      --radius-sm: 10px;
    }

    *{ box-sizing:border-box; margin:0; padding:0; -webkit-tap-highlight-color: transparent; }
    html,body{ height:100%; }

    body{
      background: #000; color: var(--text); font-family: var(--font-mono);
      display:flex; justify-content:center; align-items:flex-start; min-height:100vh; padding: 20px 10px;
    }

    svg { max-width: 100%; max-height: 100%; display: block; }

    .phone{
      position:relative; width: 100%; max-width: 392px; height: 840px; max-height: 92vh;
      background: var(--bg); border-radius: 36px; border: 1px solid var(--border);
      box-shadow: 0 30px 60px -20px rgba(0,0,0,.8); overflow: hidden; display:flex; flex-direction:column;
    }

    .flash-red-screen { animation: flashRed 0.8s infinite alternate; }
    @keyframes flashRed {
      0% { box-shadow: inset 0 0 0px rgba(255, 91, 84, 0); }
      100% { box-shadow: inset 0 0 60px rgba(255, 91, 84, 0.6); }
    }

    .statusbar{ display:flex; align-items:center; justify-content:space-between; padding: 14px 22px 4px; font-family: var(--font-display); font-size: 13px; font-weight: 600; flex-shrink:0; }
    .statusbar .icons{ display:flex; align-items:center; gap:6px; }
    .sig-bars{ display:flex; align-items:flex-end; gap:2px; height:9px; }
    .sig-bars span{ width:2.5px; background:var(--white); display:block; }
    .sig-bars span:nth-child(1){ height:40%; }
    .sig-bars span:nth-child(2){ height:60%; }
    .sig-bars span:nth-child(3){ height:80%; }
    .sig-bars span:nth-child(4){ height:100%; }
    .batt{ width:22px; height:11px; border:1.2px solid var(--text); border-radius:3px; position:relative; padding:1.5px; }
    .batt::after{ content:""; position:absolute; right:-3px; top:3px; width:2px; height:5px; background:var(--text); }
    .batt i{ display:block; width:70%; height:100%; background:var(--white); }

    .appbar{ display:flex; align-items:center; justify-content:space-between; padding: 10px 20px 14px; flex-shrink:0; border-bottom: 1px solid var(--border-soft); }
    .appbar .id{ display:flex; align-items:center; gap:10px; }
    .pulse-dot{ width:8px;height:8px;border-radius:50%; background: var(--green); animation: pulseDot 1.8s infinite; flex-shrink:0; }
    .pulse-dot.danger { background: var(--red); animation: pulseRed 1s infinite; }

    @keyframes pulseDot{ 0%{ box-shadow:0 0 0 0 rgba(74,222,128,.55); } 70%{ box-shadow:0 0 0 8px rgba(74,222,128,0); } }
    @keyframes pulseRed{ 0%{ box-shadow:0 0 0 0 rgba(255,91,84,.8); } 70%{ box-shadow:0 0 0 10px rgba(255,91,84,0); } }

    .appbar h1{ font-family: var(--font-display); font-size: 15px; font-weight:700; }
    .appbar .sub{ font-size:9.5px; color:var(--text-faint); letter-spacing:.1em; text-transform:uppercase; margin-top:2px; }

    .bell{ position:relative; width:36px;height:36px; border:1px solid var(--border); border-radius:50%; display:flex; align-items:center; justify-content:center; background: var(--surface); cursor:pointer; flex-shrink:0; }
    .bell svg{ width:16px; height:16px; }
    .bell .badge{ position:absolute; top:-3px; right:-3px; background:var(--red); color:#fff; font-size:9px; font-weight:700; width:16px; height:16px; border-radius:50%; display:flex; align-items:center; justify-content:center; }

    .scroll{ flex:1; overflow-y:auto; overflow-x:hidden; scrollbar-width:none; padding: 14px 16px; display:flex; flex-direction:column; gap:12px; }
    .scroll::-webkit-scrollbar{ display:none; }

    .alert-card{ background: var(--surface); border: 1px solid var(--border); border-left: 4px solid var(--green); border-radius: var(--radius-md); padding: 14px; display:flex; align-items:flex-start; gap: 12px; transition: all 0.3s ease; }
    .alert-card.has-crash { border-left-color: var(--red); background: rgba(255,91,84,0.08); border-color: rgba(255,91,84,0.3); }
    .alert-icon{ width:38px;height:38px; flex-shrink:0; border-radius:50%; background: var(--green-dim); display:flex; align-items:center; justify-content:center; }
    .alert-card.has-crash .alert-icon { background: var(--red-dim); color: var(--red); }
    .alert-icon svg{ width:18px; height:18px; }

    .alert-body{ flex:1; min-width:0; }
    .alert-body .title{ font-family:var(--font-display); font-weight:700; font-size:13px; color:var(--white); margin-bottom:4px; }
    .alert-body .meta{ font-size:10px; color:var(--text-dim); line-height: 1.4; word-break: break-word; }

    .alert-timer{ font-family:var(--font-mono); font-size:10px; font-weight:600; color:var(--green); flex-shrink:0; text-align:right; background: var(--surface-2); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); }
    .alert-card.has-crash .alert-timer { color: var(--red); border-color: rgba(255,91,84,0.3); }

    .map-wrap { position: relative; height: 200px; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border); flex-shrink: 0; }
    #map{ width:100%; height:100%; background: #141414; }
    .leaflet-tile { filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3); }

    .map-overlay-top{ position:absolute; top:10px; left:10px; z-index: 1000; }
    .map-chip{ background: rgba(10,10,10,.85); border:1px solid var(--border); border-radius: 999px; padding: 4px 10px; font-size:9px; color: var(--text-dim); display:flex; align-items:center; gap:5px; }
    .map-chip .live-dot{ width:5px;height:5px;border-radius:50%; background:var(--green); }
    .map-chip .live-dot.danger{ background:var(--red); }

    .fab-btn{ width:100%; background: var(--white); color: #0a0a0a; border:none; border-radius: var(--radius-md); padding: 12px 16px; font-family: var(--font-display); font-weight:700; font-size: 12px; display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer; box-shadow: 0 10px 20px rgba(255,255,255,.1); transition: opacity 0.2s; flex-shrink:0; }
    .fab-btn:active { opacity: 0.8; }
    .fab-btn svg { width: 15px; height: 15px; }

    .section-head { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; flex-shrink: 0; }
    .section-head h2 { font-family: var(--font-display); font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); }
    .section-head .count { font-size: 10px; color: var(--text-faint); }

    .unit-scroll { display: flex; gap: 10px; overflow-x: auto; overflow-y: hidden; padding: 4px 2px 8px; min-height: 85px; scrollbar-width: none; flex-shrink: 0; }
    .unit-scroll::-webkit-scrollbar { display: none; }

    .unit-card { flex-shrink: 0; width: 150px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px 12px; cursor: pointer; box-sizing: border-box; transition: all 0.2s ease; }
    .unit-card .name { font-size: 11px; font-weight: 700; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .unit-card .role { font-size: 9px; color: var(--text-faint); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .unit-card .card-stats { font-size: 9.5px; color: var(--text-dim); margin-top: 6px; line-height: 1.35; }

    .quick-stats{ display:grid; grid-template-columns: repeat(3,1fr); border:1px solid var(--border); border-radius: var(--radius-md); background: var(--surface); flex-shrink: 0; }
    .quick-stats .qs{ padding: 10px 8px; border-right:1px solid var(--border); text-align:center; }
    .quick-stats .qs:last-child{ border-right:none; }
    .quick-stats .qs .lbl{ font-size:8px; color:var(--text-faint); }
    .quick-stats .qs .val{ font-family:var(--font-display); font-size:15px; font-weight:700; margin-top:3px; }

    .dispatch-modal-backdrop{ position:absolute; inset:0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); display:flex; align-items:center; justify-content:center; padding: 20px; z-index: 3000; opacity: 0; pointer-events: none; transition: opacity 0.25s ease; }
    .dispatch-modal-backdrop.active{ opacity: 1; pointer-events: auto; }
    .dispatch-modal{ background: var(--surface); border: 1px solid var(--green); border-radius: var(--radius-md); padding: 20px; width: 100%; max-width: 320px; text-align: center; }
    .dispatch-icon{ width: 48px; height: 48px; border-radius: 50%; background: var(--green-dim); color: var(--green); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
    .dispatch-icon svg{ width: 24px; height: 24px; }
    .dispatch-title{ font-family: var(--font-display); font-size: 15px; font-weight: 700; color: var(--white); margin-bottom: 6px; }
    .dispatch-desc{ font-size: 11px; color: var(--text-dim); margin-bottom: 16px; }
    .dispatch-btn{ width: 100%; background: var(--green); color: #0b0b0b; border: none; border-radius: var(--radius-sm); padding: 10px; font-family: var(--font-display); font-weight: 700; font-size: 12px; cursor: pointer; }

    .navbar{ background: rgba(11,11,11,.95); backdrop-filter: blur(10px); border-top: 1px solid var(--border); display:flex; padding: 10px 8px; flex-shrink: 0; }
    .nav-item{ flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; color: var(--text-faint); background:none; border:none; font-size: 8.5px; cursor:pointer; }
    .nav-item svg{ width:18px; height:18px; }
    .nav-item.active{ color: var(--white); }

    .sheet-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.7); opacity: 0; pointer-events: none; transition: opacity .25s ease; z-index: 9999; }
    .sheet-backdrop.active{ opacity:1; pointer-events:auto; }

    .detail-sheet { position: absolute; left: 0; right: 0; bottom: 0; height: 85%; background: var(--surface); border-radius: 24px 24px 0 0; transform: translateY(100%); transition: transform .38s ease; z-index: 10000; display: flex; flex-direction: column; }
    .detail-sheet.active{ transform: translateY(0); }
    .sheet-handle{ width:36px; height:4px; background:var(--border); border-radius:2px; margin:10px auto 2px; }
    .sheet-header{ display:flex; align-items:center; gap:12px; padding: 10px 18px 14px; border-bottom: 1px solid var(--border-soft); }
    .sheet-avatar{ width:42px; height:42px; border-radius:50%; background: var(--surface-3); display:flex; align-items:center; justify-content:center; font-weight:700; }
    .sheet-id .name{ font-family: var(--font-display); font-weight:700; font-size:14px; }
    .sheet-id .role{ font-size:10px; color:var(--text-faint); }
    .sheet-close{ width:30px; height:30px; border-radius:50%; border:1px solid var(--border); background: var(--surface-2); display:flex; align-items:center; justify-content:center; cursor:pointer; }
    .sheet-close svg{ width:14px; height:14px; }

    .sheet-content{ flex:1; overflow-y:auto; padding: 16px 18px; }

    /* STAT DUO (Kecepatan & G-Force) Karena Max Tilt dihilangkan */
    .stat-duo{ display:grid; grid-template-columns: repeat(2,1fr); border:1px solid var(--border); border-radius: var(--radius-md); overflow:hidden; margin-bottom: 14px; }
    .stat-duo .st{ padding:12px 8px; text-align:center; border-right:1px solid var(--border); }
    .stat-duo .st:last-child{ border-right:none; }
    .stat-duo .st .lbl{ font-size:8.5px; color:var(--text-faint); }
    .stat-duo .st .val{ font-family:var(--font-display); font-size:20px; font-weight:700; margin-top:5px; }

    .detail-panel{ border:1px solid var(--border); border-radius: var(--radius-md); padding: 14px; margin-bottom: 14px; background: var(--surface-2); }

    .tilt-widget{ display:flex; align-items:center; gap:16px; }
    .tilt-stage{ position:relative; width:96px; height:72px; border-radius: 10px; background: var(--surface-3); border:1px solid var(--border); overflow:hidden; }
    .tilt-horizon{ position:absolute; left:-20%; right:-20%; top:50%; height:1px; background: repeating-linear-gradient(90deg, var(--text-faint) 0 6px, transparent 6px 11px); }
    .tilt-car{ position:absolute; left:50%; top:50%; width:44px; height:22px; transform: translate(-50%,-50%) rotate(0deg); transition: transform .35s ease; }
    .tilt-readout .deg{ font-family:var(--font-display); font-size:24px; font-weight:700; color:var(--green); }

    .history-item { display:flex; align-items:center; justify-content:space-between; padding:12px; background:var(--surface-2); border:1px solid var(--border); border-radius:var(--radius-sm); margin-bottom:8px; cursor:pointer; }
    .history-item.pending { border-left:3px solid var(--red); }
    .history-item.resolved { border-left:3px solid var(--green); }
    .history-tag { font-size:8px; padding:3px 6px; border-radius:4px; font-weight:700; }
    .history-tag.pending { background:var(--red-dim); color:var(--red); }
    .history-tag.resolved { background:var(--green-dim); color:var(--green); }
  </style>
</head>
<body>

<div class="phone" id="phone">

  <div class="statusbar">
    <span id="clock">09:41</span>
    <div class="icons">
      <div class="sig-bars"><span></span><span></span><span></span><span></span></div>
      <div class="batt"><i></i></div>
    </div>
  </div>

  <div class="appbar">
    <div class="id">
      <span class="pulse-dot" id="liveDot"></span>
      <div>
        <h1>RESPONSE CONSOLE</h1>
        <div class="sub">Multi-Incident Dispatch · Active</div>
      </div>
    </div>
    <div class="bell" id="bellBtn">
      <svg viewBox="0 0 24 24" fill="none" stroke="#f4f4f2" stroke-width="1.8"><path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
      <span class="badge" id="alertBadge">0</span>
    </div>
  </div>

  <div class="scroll">
    <div class="alert-card" id="alertCard" style="cursor:pointer;">
      <div class="alert-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
      </div>
      <div class="alert-body">
        <div class="title" id="alertTitle">Sistem Normal</div>
        <div class="meta" id="alertMeta">Semua insiden berhasil dievakuasi · Standby</div>
      </div>
      <div class="alert-timer" id="elapsedTime">--:--</div>
    </div>

    <div class="map-wrap">
      <div id="map"></div>
      <div class="map-overlay-top">
        <div class="map-chip"><span class="live-dot" id="mapLiveDot"></span> OPENSTREETMAP LIVE</div>
      </div>
    </div>

    <button class="fab-btn" id="fabBtn">
      <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0a" stroke-width="2.2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
      <span>EVAKUASI INSIDEN TERPILIH</span>
    </button>

    <div class="section-head">
      <h2>Insiden Aktif</h2>
      <span class="count" id="unitCount">0 insiden</span>
    </div>
    <div class="unit-scroll" id="unitScroll"></div>

    <div class="quick-stats">
      <div class="qs"><div class="lbl">Unit Siap</div><div class="val" id="qsReady">3</div></div>
      <div class="qs"><div class="lbl">ETA Terdekat</div><div class="val" id="qsEta">10<span>dtk</span></div></div>
      <div class="qs"><div class="lbl">Radius</div><div class="val">1.2<span>km</span></div></div>
    </div>
  </div>

  <div class="dispatch-modal-backdrop" id="dispatchModalBackdrop">
    <div class="dispatch-modal">
      <div class="dispatch-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div class="dispatch-title">Unit Berhasil Dikirim!</div>
      <div class="dispatch-desc" id="dispatchDesc">Ambulans terdekat sedang menuju lokasi kejadian...</div>
      <button class="dispatch-btn" id="dispatchCloseBtn">TUTUP</button>
    </div>
  </div>

  <div class="sheet-backdrop" id="sheetBackdrop"></div>
  <div class="detail-sheet" id="detailSheet">
    <div class="sheet-handle"></div>
    <div class="sheet-header">
      <div class="sheet-avatar" id="sheetAvatar">AF</div>
      <div class="sheet-id">
        <div class="name" id="sheetName">Ahmad Fauzi</div>
        <div class="role" id="sheetRole">RC Crash Detector Prototype · B-01</div>
      </div>
      <button class="sheet-close" id="sheetClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="#f4f4f2" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="sheet-content">
      <div class="stat-duo">
        <div class="st"><div class="lbl">Kecepatan Max</div><div class="val" id="dtSpeed">0<span>km/j</span></div></div>
        <div class="st"><div class="lbl">G‑Force Max</div><div class="val" id="dtG">0.0<span>g</span></div></div>
      </div>

      <div class="detail-panel">
        <div class="dp-head"><h3 style="font-size:11px; font-weight:700; color:var(--white);">Pitch (Dongak / Nungging)</h3><span class="tag" id="pitchTag">NORMAL</span></div>
        <div class="tilt-widget">
          <div class="tilt-stage">
            <!-- Horizon Dibuat Datar / Diam -->
            <div class="tilt-horizon" style="transform: none !important; top: 50%;"></div>
            <div class="tilt-car" id="pitchCar">
              <svg viewBox="0 0 50 26" fill="none">
                <!-- Bodi Samping Mobil -->
                <path d="M6 14C6 11 9 9 14 9H36C41 9 44 11 44 14V18H6V14Z" id="pitchBody" fill="#4ADE80"/>
                <!-- Atap Kabin -->
                <path d="M16 9L20 4H30L34 9H16Z" id="pitchRoof" fill="#4ADE80"/>
                <!-- Roda yang Proporsional -->
                <circle cx="14" cy="19" r="4" fill="#0b0b0b" stroke="#4ADE80" stroke-width="2"/>
                <circle cx="36" cy="19" r="4" fill="#0b0b0b" stroke="#4ADE80" stroke-width="2"/>
              </svg>
            </div>
          </div>
          <div class="tilt-readout">
            <div class="deg" id="pitchDegText">0.0°</div>
            <div class="dir" id="pitchDirText">Stabil / Rata</div>
          </div>
        </div>
      </div>

      <div class="detail-panel">
        <div class="dp-head"><h3 style="font-size:11px; font-weight:700; color:var(--white);">Roll (Kanan / Kiri)</h3><span class="tag" id="rollTag">NORMAL</span></div>
        <div class="tilt-widget">
          <div class="tilt-stage">
            <!-- Horizon Dibuat Datar / Diam -->
            <div class="tilt-horizon" style="transform: none !important; top: 50%;"></div>
            <div class="tilt-car" id="rollCar">
              <svg viewBox="0 0 36 28" fill="none">
                <!-- Bodi Depan/Belakang Mobil -->
                <rect x="5" y="10" width="26" height="12" rx="4" id="rollBody" fill="#4ADE80"/>
                <!-- Kabin Atas -->
                <rect x="9" y="4" width="18" height="7" rx="3" id="rollRoof" fill="#4ADE80"/>
                <!-- Lampu / Detail -->
                <rect x="8" y="14" width="3" height="2" rx="1" fill="#0b0b0b"/>
                <rect x="25" y="14" width="3" height="2" rx="1" fill="#0b0b0b"/>
                <!-- Roda Kiri & Kanan yang Rapi -->
                <circle cx="7" cy="23" r="3.5" fill="#0b0b0b" stroke="#4ADE80" stroke-width="2"/>
                <circle cx="29" cy="23" r="3.5" fill="#0b0b0b" stroke="#4ADE80" stroke-width="2"/>
              </svg>
            </div>
          </div>
          <div class="tilt-readout">
            <div class="deg" id="rollDegText">0.0°</div>
            <div class="dir" id="rollDirText">Stabil / Rata</div>
          </div>
        </div>
      </div>

      <div class="detail-panel">
        <div class="dp-head"><h3 style="font-size:11px; font-weight:700; color:var(--white);">Accelerometer Axis (G-Force X/Y/Z)</h3></div>
        <div style="height: 160px; position: relative;"><canvas id="accelChart"></canvas></div>
      </div>

      <div class="detail-panel">
        <div class="dp-head"><h3 style="font-size:11px; font-weight:700; color:var(--white);">Gyroscope Rate (°/s)</h3></div>
        <div style="height: 160px; position: relative;"><canvas id="gyroChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="sheet-backdrop" id="historyBackdrop"></div>
  <div class="detail-sheet" id="historySheet">
    <div class="sheet-handle"></div>
    <div class="sheet-header">
      <div class="sheet-id">
        <div class="name">📜 Riwayat Kecelakaan</div>
        <div class="role">Semua Log Insiden Database</div>
      </div>
      <button class="sheet-close" id="historyClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="#f4f4f2" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="sheet-content" id="historyListContent"></div>
  </div>

  <div class="navbar">
    <button class="nav-item active" id="navMap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/></svg>PETA</button>
    <button class="nav-item" id="navAlert"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z"/></svg>ALERT & HISTORY</button>
    <button class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="7" width="15" height="11" rx="1.5"/></svg>UNIT</button>
    <button class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>PROFIL</button>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map, routeLine, ambulanceMarker;
let accidentMarkers = {};
let pendingAccidents = [];
let selectedAccident = null;
let accelChart = null, gyroChart = null;

const units = [
  { id:'U-04', name:'Rian S.', role:'Ambulans', lat: -6.2030, lng: 106.8400, dist:'0.4', eta:'1', status:'ready' },
  { id:'U-11', name:'Dwi P.',  role:'Motor Sar', lat: -6.2110, lng: 106.8500, dist:'0.6', eta:'2', status:'ready' },
  { id:'U-02', name:'Budi H.', role:'Ambulans', lat: -6.2000, lng: 106.8350, dist:'0.9', eta:'3', status:'ready' },
  { id:'U-07', name:'Sinta A.',role:'Damkar',   lat: -6.2200, lng: 106.8420, dist:'1.1', eta:'5', status:'enroute' }
];

function initLeafletMap() {
  map = L.map('map', { zoomControl: false, attributionControl: false }).setView([-6.2030, 106.8400], 14);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

  const greenIcon = L.divIcon({
    className: 'custom-div-icon',
    html: `<div style="background-color:#4ADE80;width:10px;height:10px;border-radius:50%;border:2px solid white;box-shadow:0 0 8px #4ADE80;"></div>`,
    iconSize: [10, 10], iconAnchor: [5, 5]
  });

  units.forEach(u => {
    L.marker([u.lat, u.lng], { icon: greenIcon }).addTo(map);
  });
}

function initTelemetryCharts(data) {
  const gForce = parseFloat(data.g_force || 0);
  const labels = ['t-4s', 't-3s', 't-2s', 't-1s', 'IMPACT', 't+1s', 't+2s'];

  const ctxAccel = document.getElementById('accelChart').getContext('2d');
  if (accelChart) accelChart.destroy();
  accelChart = new Chart(ctxAccel, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        { label: 'Accel X', data: [0.1, 0.2, 0.3, 0.4, gForce * 0.9, 0.2, 0.1], borderColor: '#FF5B54', borderWidth: 1.8, tension: 0.3 },
        { label: 'Accel Y', data: [0.0, 0.1, 0.2, 0.3, gForce * 0.6, 0.3, 0.1], borderColor: '#3B82F6', borderWidth: 1.8, tension: 0.3 },
        { label: 'Accel Z', data: [1.0, 1.0, 1.1, 1.2, gForce * 1.2, 1.1, 1.0], borderColor: '#4ADE80', borderWidth: 1.8, tension: 0.3 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#a3a29e', font: { size: 8.5 } } } },
      scales: { x: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 8 } } }, y: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 8 } } } }
    }
  });

  const ctxGyro = document.getElementById('gyroChart').getContext('2d');
  if (gyroChart) gyroChart.destroy();
  gyroChart = new Chart(ctxGyro, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        { label: 'Gyro Roll', data: [0, 5, 12, 25, 30, 10, 5], borderColor: '#EAB308', borderWidth: 1.8, tension: 0.3 },
        { label: 'Gyro Pitch', data: [0, 2, 8, 15, 20, 8, 2], borderColor: '#EC4899', borderWidth: 1.8, tension: 0.3 },
        { label: 'Gyro Yaw', data: [0, 1, 4, 10, 15, 4, 1], borderColor: '#A855F7', borderWidth: 1.8, tension: 0.3 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#a3a29e', font: { size: 8.5 } } } },
      scales: { x: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 8 } } }, y: { grid: { color: '#2a2a2a' }, ticks: { color: '#6b6a66', font: { size: 8 } } } }
    }
  });
}

function formatTime(timeString) {
  if (!timeString) return 'LIVE';
  try {
    const d = new Date(timeString);
    if (isNaN(d.getTime())) return 'LIVE';
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  } catch(e) {
    return 'LIVE';
  }
}

document.addEventListener("DOMContentLoaded", function() {
  initLeafletMap();
  checkLaravelStatus();
  setInterval(checkLaravelStatus, 2000);
});

let audioCtx, isPlaying = false, sirenInterval;
function playSirenSound() {
  if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  if (isPlaying) return;
  isPlaying = true;
  let highTone = true;
  sirenInterval = setInterval(() => {
    if (!isPlaying) return;
    let osc = audioCtx.createOscillator(), gain = audioCtx.createGain();
    osc.type = 'sine';
    let freq = highTone ? 900 : 600;
    osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
    gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.45);
    osc.connect(gain); gain.connect(audioCtx.destination);
    osc.start(); osc.stop(audioCtx.currentTime + 0.45);
    highTone = !highTone;
  }, 480);
}

function stopAlarm() {
  if (sirenInterval) clearInterval(sirenInterval);
  isPlaying = false;
}

function speakVoice(text) {
  if ('speechSynthesis' in window) {
    let utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    window.speechSynthesis.speak(utterance);
  }
}

let lastLogId = null;
function checkLaravelStatus() {
  fetch('/api/pending-accidents')
    .then(res => res.json())
    .then(res => {
      const count = res.count || 0;
      pendingAccidents = res.data || [];

      const phone = document.getElementById('phone');
      const card = document.getElementById('alertCard');
      const title = document.getElementById('alertTitle');
      const meta = document.getElementById('alertMeta');
      const time = document.getElementById('elapsedTime');
      const dot = document.getElementById('liveDot');
      const mapDot = document.getElementById('mapLiveDot');
      const badge = document.getElementById('alertBadge');

      badge.textContent = count;
      document.getElementById('unitCount').textContent = `${count} kecelakaan pending`;

      if (count > 0) {
        if (!selectedAccident || !pendingAccidents.find(i => i.id === selectedAccident.id)) {
          selectedAccident = pendingAccidents[0];
        }

        const data = selectedAccident;
        const pitchVal = parseFloat(data.pitch || 0);
        const rollVal = parseFloat(data.roll || 0);
        const gVal = parseFloat(data.g_force || 0);

        phone.classList.add('flash-red-screen');
        card.classList.add('has-crash');
        dot.classList.add('danger');
        if(mapDot) mapDot.classList.add('danger');

        title.innerHTML = `🚨 KECELAKAAN PENDING (${count})`;
        meta.innerHTML = `
          Terpilih: <b>${data.name || 'Ahmad Fauzi'}</b> (${data.vehicle || 'RC Crash'})<br>
          Pitch: <b>${pitchVal.toFixed(1)}°</b> | Roll: <b>${rollVal.toFixed(1)}°</b>
        `;
        time.textContent = formatTime(data.created_at);

        if (lastLogId !== data.id) {
          lastLogId = data.id;
          playSirenSound();
          speakVoice(`Peringatan! Terjadi kecelakaan baru pada kendaraan ${data.name}.`);
        }

        updateSheetTelemetry(data);
        renderAccidentMarkersOnMap();
        renderIncidentCardsList();

      } else {
        phone.classList.remove('flash-red-screen');
        card.classList.remove('has-crash');
        dot.classList.remove('danger');
        if(mapDot) mapDot.classList.remove('danger');

        title.textContent = 'Sistem Normal';
        meta.innerHTML = 'Semua insiden berhasil dievakuasi · Standby';
        time.textContent = '--:--';
        badge.textContent = "0";

        selectedAccident = null;
        lastLogId = null;
        stopAlarm();

        clearAccidentMarkers();
        renderIncidentCardsList();
      }
    })
    .catch(err => console.log('Waiting for incident data...', err));
}

function renderAccidentMarkersOnMap() {
  clearAccidentMarkers();

  pendingAccidents.forEach(item => {
    const itemLat = parseFloat(item.lat);
    const itemLng = parseFloat(item.lng);

    if (isNaN(itemLat) || isNaN(itemLng)) return;

    const isSelected = selectedAccident && selectedAccident.id === item.id;
    const color = isSelected ? '#FF5B54' : '#FFA500';

    const icon = L.divIcon({
      className: 'custom-div-icon',
      html: `<div style="background-color:${color};width:16px;height:16px;border-radius:50%;border:2px solid white;box-shadow:0 0 10px ${color};"></div>`,
      iconSize: [16, 16],
      iconAnchor: [8, 8]
    });

    let m = L.marker([itemLat, itemLng], { icon: icon }).addTo(map);
    m.bindPopup(`<b>KECELAKAAN #${item.id}</b><br>${item.name} - ${item.vehicle}<br>G-Force: ${item.g_force}g`);
    accidentMarkers[item.id] = m;
  });

  if (selectedAccident) {
    const selLat = parseFloat(selectedAccident.lat);
    const selLng = parseFloat(selectedAccident.lng);
    if (!isNaN(selLat) && !isNaN(selLng)) {
      map.flyTo([selLat, selLng], 15);
    }
  }
}

function clearAccidentMarkers() {
  Object.keys(accidentMarkers).forEach(k => map.removeLayer(accidentMarkers[k]));
  accidentMarkers = {};
}

function renderIncidentCardsList() {
  const scroll = document.getElementById('unitScroll');
  scroll.innerHTML = '';

  if (pendingAccidents.length === 0) {
    units.forEach(u => {
      const card = document.createElement('div');
      card.className = 'unit-card';
      card.innerHTML = `
        <div class="name">${u.name}</div>
        <div class="role">${u.role}</div>
      `;
      scroll.appendChild(card);
    });
    return;
  }

  pendingAccidents.forEach((item) => {
    const isSelected = selectedAccident && selectedAccident.id === item.id;
    const card = document.createElement('div');
    card.className = 'unit-card';
    card.style.borderColor = isSelected ? '#FF5B54' : '#2a2a2a';
    card.style.background = isSelected ? 'rgba(255, 91, 84, 0.12)' : 'var(--surface)';

    card.onclick = () => {
      selectedAccident = item;
      checkLaravelStatus();
    };

    const pitchVal = parseFloat(item.pitch || 0);
    const rollVal = parseFloat(item.roll || 0);
    const gVal = parseFloat(item.g_force || 0);

    card.innerHTML = `
      <div class="name">#${item.id} ${item.name || 'Ahmad Fauzi'}</div>
      <div class="role">${item.vehicle || 'RC Crash'}</div>
      <div class="card-stats">
        Pitch: <b>${pitchVal.toFixed(1)}°</b><br>
        Roll: <b>${rollVal.toFixed(1)}°</b>
      </div>
    `;
    scroll.appendChild(card);
  });
}

function updateSheetTelemetry(data) {
  if (!data) return;

  const pitchVal = parseFloat(data.pitch || 0);
  const rollVal = parseFloat(data.roll || 0);
  const speedVal = parseFloat(data.speed || 0);
  const gVal = parseFloat(data.g_force || 0);

  document.getElementById('sheetName').textContent = data.name || 'Ahmad Fauzi';
  document.getElementById('sheetRole').textContent = `${data.vehicle || 'RC Crash Detector'} · ${data.unit_id || 'B-01'}`;

  document.getElementById('dtSpeed').innerHTML = `${speedVal.toFixed(1)}<span>km/j</span>`;
  document.getElementById('dtG').innerHTML = `${gVal.toFixed(1)}<span>g</span>`;

  // --- UPDATE WIDGET PITCH (DONGAK / NUNGGING) ---
  const pitchCar = document.getElementById('pitchCar');
  const pitchHorizon = document.getElementById('pitchHorizon');
  const pitchDegText = document.getElementById('pitchDegText');
  const pitchDirText = document.getElementById('pitchDirText');
  const pitchTag = document.getElementById('pitchTag');

  if (pitchCar) pitchCar.style.transform = `translate(-50%,-50%) rotate(${-pitchVal}deg)`;
  if (pitchHorizon) pitchHorizon.style.transform = `rotate(${-pitchVal * 0.15}deg)`;
  if (pitchDegText) {
    pitchDegText.textContent = `${pitchVal.toFixed(1)}°`;
    pitchDegText.style.color = Math.abs(pitchVal) > 30 ? '#FF5B54' : '#4ADE80';
  }

  if (Math.abs(pitchVal) > 30) {
    if (pitchDirText) pitchDirText.textContent = pitchVal > 0 ? 'Dongak Ekstrem (Depan Naik)' : 'Nungging Ekstrem (Depan Turun)';
    if (pitchTag) {
      pitchTag.textContent = 'OVER LIMIT';
      pitchTag.style.color = '#FF5B54';
    }
    document.getElementById('pitchBody')?.setAttribute('fill', '#FF5B54');
    document.getElementById('pitchRoof')?.setAttribute('fill', '#FF5B54');
  } else {
    if (pitchDirText) pitchDirText.textContent = Math.abs(pitchVal) < 3 ? 'Stabil / Rata' : 'Kemiringan Normal';
    if (pitchTag) {
      pitchTag.textContent = 'NORMAL';
      pitchTag.style.color = '#4ADE80';
    }
    document.getElementById('pitchBody')?.setAttribute('fill', '#4ADE80');
    document.getElementById('pitchRoof')?.setAttribute('fill', '#4ADE80');
  }

  // --- UPDATE WIDGET ROLL (KANAN / KIRI) ---
  const rollCar = document.getElementById('rollCar');
  const rollHorizon = document.getElementById('rollHorizon');
  const rollDegText = document.getElementById('rollDegText');
  const rollDirText = document.getElementById('rollDirText');
  const rollTag = document.getElementById('rollTag');

  if (rollCar) rollCar.style.transform = `translate(-50%,-50%) rotate(${-rollVal}deg)`;
  if (rollHorizon) rollHorizon.style.transform = `rotate(${-rollVal * 0.15}deg)`;
  if (rollDegText) {
    rollDegText.textContent = `${rollVal.toFixed(1)}°`;
    rollDegText.style.color = Math.abs(rollVal) > 30 ? '#FF5B54' : '#4ADE80';
  }

  if (Math.abs(rollVal) > 30) {
    if (rollDirText) rollDirText.textContent = rollVal > 0 ? 'Miring Kiri Ekstrem' : 'Miring Kanan Ekstrem';
    if (rollTag) {
      rollTag.textContent = 'OVER LIMIT';
      rollTag.style.color = '#FF5B54';
    }
    document.getElementById('rollBody')?.setAttribute('fill', '#FF5B54');
    document.getElementById('rollRoof')?.setAttribute('fill', '#FF5B54');
  } else {
    if (rollDirText) rollDirText.textContent = Math.abs(rollVal) < 3 ? 'Stabil / Rata' : 'Kemiringan Normal';
    if (rollTag) {
      rollTag.textContent = 'NORMAL';
      rollTag.style.color = '#4ADE80';
    }
    document.getElementById('rollBody')?.setAttribute('fill', '#4ADE80');
    document.getElementById('rollRoof')?.setAttribute('fill', '#4ADE80');
  }

  initTelemetryCharts(data);
}

function animateAmbulanceToAccident() {
  if (!selectedAccident) return;

  stopAlarm();
  const targetId = selectedAccident.id;
  let accidentLat = parseFloat(selectedAccident.lat);
  let accidentLng = parseFloat(selectedAccident.lng);

  let ambulance = units[0];
  let startLat = ambulance.lat, startLng = ambulance.lng;
  let steps = 60;
  let currentStep = 0;

  document.getElementById('alertTitle').textContent = 'AMBULANS DALAM PERJALANAN';
  document.getElementById('alertMeta').innerHTML = `Evakuasi <b>${selectedAccident.name}</b> (ETA 6 Dtk)...`;

  if (routeLine) map.removeLayer(routeLine);
  routeLine = L.polyline([[startLat, startLng], [accidentLat, accidentLng]], { color: '#00BFFF', weight: 4, dashArray: '6, 6' }).addTo(map);

  let ambIcon = L.divIcon({
    className: 'custom-div-icon',
    html: `<div style="background-color:#00BFFF;width:16px;height:16px;border-radius:50%;border:2px solid white;box-shadow:0 0 10px #00BFFF;display:flex;align-items:center;justify-content:center;font-size:10px;">🚑</div>`,
    iconSize: [16, 16], iconAnchor: [8, 8]
  });

  if (ambulanceMarker) map.removeLayer(ambulanceMarker);
  ambulanceMarker = L.marker([startLat, startLng], { icon: ambIcon }).addTo(map);

  speakVoice("Unit ambulans terdekat telah dikirim menuju lokasi kejadian.");

  let moveInterval = setInterval(() => {
    currentStep++;
    let lat = startLat + (accidentLat - startLat) * (currentStep / steps);
    let lng = startLng + (accidentLng - startLng) * (currentStep / steps);

    ambulanceMarker.setLatLng([lat, lng]);

    if (currentStep >= steps) {
      clearInterval(moveInterval);

      if (routeLine) map.removeLayer(routeLine);
      if (ambulanceMarker) map.removeLayer(ambulanceMarker);

      fetch(`/api/resolve-accident/${targetId}`)
        .then(res => res.json())
        .then(data => {
          selectedAccident = null;
          speakVoice("Ambulans telah tiba. Korban berhasil dievakuasi.");
          checkLaravelStatus();
        });
    }
  }, 100);
}

function fetchAndOpenHistory() {
  fetch('/api/accidents')
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById('historyListContent');
      container.innerHTML = '';

      if (!data || data.length === 0) {
        container.innerHTML = '<div style="font-size:11px; text-align:center; padding:20px; color:var(--text-faint);">Belum ada riwayat kecelakaan.</div>';
      } else {
        data.forEach(item => {
          const div = document.createElement('div');
          div.className = `history-item ${item.status}`;

          div.onclick = () => {
            selectedAccident = item;
            updateSheetTelemetry(item);

            const latVal = parseFloat(item.lat);
            const lngVal = parseFloat(item.lng);
            if (!isNaN(latVal) && !isNaN(lngVal)) {
              map.flyTo([latVal, lngVal], 16);
            }

            closeHistorySheet();
            document.getElementById('sheetBackdrop').classList.add('active');
            document.getElementById('detailSheet').classList.add('active');
          };

          div.innerHTML = `
            <div>
              <div style="font-size:12px; font-weight:700; color:var(--white);">#${item.id} - ${item.name}</div>
              <div style="font-size:10px; color:var(--text-dim); margin-top:2px;">${item.vehicle} · G-Force: ${item.g_force}g</div>
              <div style="font-size:8.5px; color:var(--text-faint); margin-top:4px;">${item.created_at}</div>
            </div>
            <span class="history-tag ${item.status}">${item.status.toUpperCase()}</span>
          `;
          container.appendChild(div);
        });
      }

      document.getElementById('historyBackdrop').classList.add('active');
      document.getElementById('historySheet').classList.add('active');
    })
    .catch(err => alert("Lengkapi Endpoint API /api/accidents terlebih dahulu di Web Routes."));
}

function closeHistorySheet() {
  document.getElementById('historyBackdrop').classList.remove('active');
  document.getElementById('historySheet').classList.remove('active');
}

document.getElementById('alertCard').addEventListener('click', () => {
  document.getElementById('sheetBackdrop').classList.add('active');
  document.getElementById('detailSheet').classList.add('active');
});

document.getElementById('sheetClose').addEventListener('click', () => {
  document.getElementById('sheetBackdrop').classList.remove('active');
  document.getElementById('detailSheet').classList.remove('active');
});

document.getElementById('bellBtn').addEventListener('click', fetchAndOpenHistory);
document.getElementById('navAlert').addEventListener('click', fetchAndOpenHistory);
document.getElementById('historyClose').addEventListener('click', closeHistorySheet);
document.getElementById('historyBackdrop').addEventListener('click', closeHistorySheet);

document.getElementById('fabBtn').addEventListener('click', () => {
  if (!selectedAccident) {
    alert("Tidak ada insiden kecelakaan aktif untuk dievakuasi.");
    return;
  }
  document.getElementById('dispatchModalBackdrop').classList.add('active');
  animateAmbulanceToAccident();
});

document.getElementById('dispatchCloseBtn').addEventListener('click', () => {
  document.getElementById('dispatchModalBackdrop').classList.remove('active');
});
</script>
</body>
</html>
