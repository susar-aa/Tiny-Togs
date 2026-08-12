<?php
$host = 'localhost';
$dbname = 'tiny_togs';
$username = 'suzxlabs';
$password = 'Susara@200611003614';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ATTR_ERRMODE,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed.");
}

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    if ($action === 'search_product') {
        $term = $_GET['term'] ?? '';
        $stmt = $pdo->prepare("SELECT product_name FROM label_products WHERE product_name LIKE :term ORDER BY product_name ASC LIMIT 10");
        $stmt->execute(['term' => '%' . $term . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
    if ($action === 'save_product') {
        $name = trim($_POST['product_name'] ?? '');
        if ($name) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO label_products (product_name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Printing — Tiny Togs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,500&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* ── Palette: warm paper + working sage, decoder gets its own muted slate ── */
            --paper: #FAF6EE;
            --surface: #FFFFFF;
            --surface-2: #F2ECDF;
            --ink: #23262A;
            --ink-soft: #6F7268;
            --ink-faint: #A6A697;
            --line: #E7DFCE;
            --line-soft: #EFE9DA;

            --sage: #4E7160;
            --sage-deep: #395344;
            --sage-tint: #E7EFE7;

            --dusk: #556C8C;
            --dusk-deep: #3E5471;
            --dusk-tint: #E9EEF4;

            --amber: #BD8A3F;
            --amber-tint: #F5EAD6;

            --danger: #B14A3B;
            --danger-tint: #F6E5E0;

            --radius-lg: 20px;
            --radius-md: 13px;
            --radius-sm: 9px;

            --shadow-card: 0 1px 2px rgba(35,38,42,0.04), 0 10px 26px -14px rgba(35,38,42,0.16);
            --shadow-label: 0 22px 44px -16px rgba(35,38,42,0.30), 0 3px 8px rgba(35,38,42,0.10);

            --font-display: 'Fraunces', Georgia, serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        html, body { height: 100%; overflow: hidden; }

        body {
            font-family: var(--font-body);
            background: var(--paper);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP NAV ── */
        .topnav {
            height: 60px;
            background: rgba(250,246,238,0.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
            flex-shrink: 0;
            z-index: 100;
        }
        .brand { display: flex; align-items: center; gap: 0.7rem; }
        .brand-mark {
            width: 34px; height: 34px;
            background: var(--sage);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-family: var(--font-display); font-weight: 700; font-size: 0.85rem;
            transform: rotate(-3deg);
            box-shadow: 0 3px 8px rgba(78,113,96,0.35);
        }
        .brand-text { display: flex; flex-direction: column; line-height: 1.15; }
        .brand-eyebrow { font-size: 0.66rem; font-weight: 700; letter-spacing: 0.11em; text-transform: uppercase; color: var(--ink-faint); }
        .brand-title { font-family: var(--font-display); font-style: italic; font-weight: 600; font-size: 1.08rem; letter-spacing: -0.01em; color: var(--ink); }
        .topnav-spacer { flex: 1; }
        .nav-btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.5rem 0.95rem; border-radius: 980px;
            font-size: 0.8rem; font-weight: 600; cursor: pointer;
            border: none; text-decoration: none; transition: 0.18s ease;
            font-family: var(--font-body);
        }
        .nav-btn-ghost { background: transparent; color: var(--ink-soft); border: 1.5px solid var(--line); }
        .nav-btn-ghost:hover { background: var(--surface-2); color: var(--ink); }
        .nav-btn-solid { background: var(--sage); color: #fff; box-shadow: 0 3px 10px rgba(78,113,96,0.3); }
        .nav-btn-solid:hover { background: var(--sage-deep); color: #fff; }

        /* ── MAIN LAYOUT ── */
        .dashboard {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 392px;
            gap: 0;
            overflow: hidden;
            height: calc(100vh - 60px);
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            overflow-y: auto;
            padding: 1.75rem 1.75rem 0;
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
        }
        .left-inner { max-width: 620px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 1.15rem; }
        .left-panel::-webkit-scrollbar { width: 6px; }
        .left-panel::-webkit-scrollbar-track { background: transparent; }
        .left-panel::-webkit-scrollbar-thumb { background: var(--line); border-radius: 3px; }

        /* ── RIGHT PANEL ── */
        .right-panel {
            border-left: 1px solid var(--line);
            background: var(--surface);
            overflow-y: auto;
        }
        .right-panel::-webkit-scrollbar { width: 5px; }
        .right-panel::-webkit-scrollbar-track { background: transparent; }
        .right-panel::-webkit-scrollbar-thumb { background: var(--line); border-radius: 3px; }

        /* ── PREVIEW STAGE (sticky, signature element) ── */
        .preview-stage {
            position: sticky; top: 0; z-index: 20;
            background: linear-gradient(180deg, var(--surface) 78%, rgba(255,255,255,0));
            padding: 1.4rem 1.4rem 1.6rem;
            border-bottom: 1px solid var(--line-soft);
        }
        .preview-eyebrow {
            font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--ink-faint); text-align: center; margin-bottom: 0.9rem;
        }
        .preview-eyebrow i { color: var(--sage); margin-right: 0.35rem; }

        .label-stage {
            display: flex; align-items: center; justify-content: center;
            padding: 0.4rem 0 0.2rem;
        }
        .sticker-preview {
            position: relative;
            width: 236px; height: 122px;
            background: #fff;
            border-radius: 4px;
            padding: 13px 15px;
            font-family: Arial, sans-serif;
            display: flex; flex-direction: column; justify-content: center;
            box-shadow: var(--shadow-label);
            transition: transform 0.25s ease;
        }
        .sticker-preview::before {
            content: ''; position: absolute; inset: 5px;
            border: 1.5px dashed #c7c2b3; border-radius: 2px; pointer-events: none;
        }
        .sticker-preview::after {
            content: ''; position: absolute; top: -1px; right: -1px;
            width: 26px; height: 26px;
            background: linear-gradient(135deg, transparent 49.5%, var(--paper) 50.5%);
            box-shadow: -2px 2px 5px rgba(35,38,42,0.12);
        }
        .label-stage:hover .sticker-preview { transform: rotate(-1.1deg) translateY(-2px); }
        @media (prefers-reduced-motion: reduce) { .sticker-preview { transition: none; } .label-stage:hover .sticker-preview { transform: none; } }

        .sticker-preview .sp-name {
            font-size: 11.5px; font-weight: 800; line-height: 1.15;
            text-transform: uppercase; margin-bottom: 7px; color: #111;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .sticker-preview .sp-date { font-size: 9.5px; font-weight: 700; line-height: 1.55; color: #222; letter-spacing: 0.01em; }

        /* ── SECTION CARD ── */
        .section-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid var(--line-soft);
            overflow: visible;
        }
        .section-card-header {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 1.05rem 1.25rem 0;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--ink-faint);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .section-card-header i { color: var(--sage); font-size: 0.78rem; }
        .section-card-header.dusk i { color: var(--dusk); }
        .section-card-body { padding: 0.8rem 1.25rem 1.25rem; }
        .section-card-body.split { display: grid; grid-template-columns: 1fr 1fr; gap: 1.4rem; }

        @media (max-width: 720px) { .section-card-body.split { grid-template-columns: 1fr; } }

        /* ── FORM ELEMENTS ── */
        label.field-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--ink-soft);
            margin-bottom: 0.45rem;
            letter-spacing: 0.01em;
        }
        .field-input {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid var(--line);
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 0.92rem;
            color: var(--ink);
            background: #fff;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .field-input:focus { border-color: var(--sage); box-shadow: 0 0 0 3px var(--sage-tint); }
        .field-input::placeholder { color: var(--ink-faint); }

        /* ── DATE GRID ── */
        .date-dropdowns { display: flex; gap: 0.5rem; }
        .date-dropdown { position: relative; flex: 1; }
        .date-display {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.62rem 0.7rem;
            border: 1.5px solid var(--line);
            border-radius: var(--radius-sm);
            background: #fff; cursor: pointer;
            font-family: var(--font-mono);
            font-size: 0.82rem; font-weight: 600; color: var(--ink);
            transition: border-color 0.18s;
            user-select: none;
        }
        .date-display:hover, .date-display.open { border-color: var(--sage); }
        .date-display .dd-arrow { font-size: 0.62rem; color: var(--ink-faint); transition: transform 0.2s; }
        .date-display.open .dd-arrow { transform: rotate(180deg); }
        .date-grid-popup {
            display: none;
            position: absolute; top: calc(100% + 5px); left: 0;
            z-index: 500; background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 8px; box-shadow: 0 14px 36px rgba(35,38,42,0.16);
        }
        .date-grid-popup.active { display: block; }
        .grid-container { display: grid; gap: 4px; }
        .grid-years { grid-template-columns: repeat(4, 1fr); width: 252px; }
        .grid-months { grid-template-columns: repeat(4, 1fr); width: 216px; }
        .grid-days { grid-template-columns: repeat(7, 1fr); width: 252px; }
        .grid-btn {
            border: none; background: var(--surface-2);
            border-radius: 7px; padding: 7px 0;
            text-align: center; cursor: pointer;
            font-family: var(--font-mono);
            font-size: 0.78rem; font-weight: 600; color: var(--ink);
            transition: 0.15s;
        }
        .grid-btn:hover { background: var(--line); }
        .grid-btn.selected { background: var(--sage); color: #fff; }

        /* ── SUGGESTION PILLS ── */
        .pills { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.6rem; }
        .pill {
            padding: 0.32rem 0.7rem;
            border-radius: 980px; border: none;
            background: var(--sage-tint); color: var(--sage-deep);
            font-size: 0.78rem; font-weight: 600; cursor: pointer;
            transition: 0.15s; font-family: var(--font-body);
        }
        .pill:hover { background: #d6e6dc; }

        /* ── AUTOCOMPLETE ── */
        .ac-wrap { position: relative; }
        .ac-list {
            position: absolute; z-index: 400; width: 100%;
            background: #fff; border: 1px solid var(--line);
            border-radius: 11px; box-shadow: 0 14px 32px rgba(35,38,42,0.12);
            max-height: 190px; overflow-y: auto; display: none; top: calc(100% + 5px);
        }
        .ac-item {
            padding: 0.6rem 0.9rem; cursor: pointer;
            font-size: 0.88rem; color: var(--ink);
            border-bottom: 1px solid var(--line-soft);
        }
        .ac-item:hover { background: var(--sage-tint); }
        .ac-item:last-child { border-bottom: none; }

        /* ── QTY ── */
        .qty-row { display: flex; align-items: center; gap: 0.75rem; }
        .qty-btn {
            width: 38px; height: 38px; border: none;
            background: var(--surface-2); border-radius: 50%;
            font-size: 1.25rem; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--sage-deep); transition: 0.15s; flex-shrink: 0;
        }
        .qty-btn:hover { background: var(--sage-tint); }
        .qty-input {
            width: 66px; text-align: center;
            border: 1.5px solid var(--line); border-radius: var(--radius-sm);
            padding: 0.5rem; font-size: 1.15rem; font-weight: 700;
            font-family: var(--font-mono); color: var(--ink); outline: none;
        }
        .qty-input:focus { border-color: var(--sage); }

        /* ── PRINT BUTTON (sticky footer of left panel) ── */
        .print-footer {
            position: sticky; bottom: 0;
            background: linear-gradient(180deg, rgba(250,246,238,0), var(--paper) 30%);
            padding: 1rem 0 1.5rem;
            margin-top: -0.5rem;
        }
        .print-btn {
            max-width: 620px; margin: 0 auto;
            width: 100%; padding: 0.95rem;
            background: var(--sage); color: #fff;
            border: none; border-radius: 14px;
            font-family: var(--font-body); font-size: 0.98rem; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            gap: 0.55rem; box-shadow: 0 10px 26px -8px rgba(78,113,96,0.5);
            transition: 0.2s;
        }
        .print-btn:hover { background: var(--sage-deep); transform: translateY(-1px); }
        .print-btn:active { transform: translateY(0); }

        /* ── DECODER PANEL ── */
        .decoder-wrap { padding: 0.25rem 1.4rem 1.6rem; display: flex; flex-direction: column; gap: 1.1rem; }
        .decoder-card-header {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--dusk-deep); margin-bottom: 0.7rem;
        }
        .decoder-card-header i { color: var(--dusk); }

        .decoder-input-row { display: flex; gap: 0.5rem; }
        .decoder-input {
            flex: 1; padding: 0.65rem 0.85rem; border: 1.5px solid var(--line);
            border-radius: var(--radius-sm); font-size: 1rem; font-family: var(--font-mono);
            outline: none; transition: border-color 0.18s, box-shadow 0.18s; letter-spacing: 0.06em; font-weight: 600;
            color: var(--ink);
        }
        .decoder-input:focus { border-color: var(--dusk); box-shadow: 0 0 0 3px var(--dusk-tint); }
        .decode-btn {
            padding: 0.65rem 1.05rem; background: var(--dusk); color: #fff;
            border: none; border-radius: var(--radius-sm); font-family: var(--font-body);
            font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: 0.18s;
            white-space: nowrap;
        }
        .decode-btn:hover { background: var(--dusk-deep); }

        .decode-result {
            background: var(--sage-tint); border-radius: 12px;
            padding: 0.95rem; display: none;
        }
        .decode-result.show { display: block; }
        .decode-result-label { font-size: 0.7rem; color: var(--sage-deep); font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 0.25rem; }
        .decode-result-date { font-family: var(--font-display); font-size: 1.35rem; font-weight: 600; color: var(--ink); letter-spacing: -0.01em; }
        .apply-btn {
            width: 100%; margin-top: 0.7rem; padding: 0.6rem;
            background: var(--sage); color: #fff;
            border: none; border-radius: 9px; font-family: var(--font-body);
            font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: 0.18s;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }
        .apply-btn:hover { background: var(--sage-deep); }

        .decode-error { display: none; color: var(--danger); background: var(--danger-tint); border-radius: 10px; font-size: 0.82rem; font-weight: 600; text-align: center; padding: 0.65rem; }
        .decode-error.show { display: block; }

        .info-box {
            background: var(--dusk-tint); border-radius: 12px;
            padding: 0.95rem; font-size: 0.8rem; color: var(--ink-soft); line-height: 1.55;
        }
        .info-box strong { color: var(--dusk-deep); }

        .refbox { background: var(--surface-2); border-radius: 12px; padding: 0.95rem; }
        .refbox-title { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--ink-faint); letter-spacing: 0.08em; margin-bottom: 0.55rem; }
        .refbox-body { font-size: 0.8rem; color: var(--ink-soft); line-height: 1.7; font-family: var(--font-mono); }
        .refbox-body strong { color: var(--ink); font-family: var(--font-body); }

        /* ── @MEDIA PRINT (physical label output — left as-is) ── */
        @media print {
            body * { visibility: hidden; }
            #print-container, #print-container * { visibility: visible; }
            #print-container {
                position: absolute; left: 0; top: 0;
                width: 100mm; margin: 0; padding: 0;
                display: flex; flex-wrap: wrap; align-content: flex-start;
            }
            .print-label {
                width: 50mm; height: 25mm;
                box-sizing: border-box; padding: 2mm 3mm; overflow: hidden;
                font-family: Arial, sans-serif; color: #000; background: #fff;
                display: flex; flex-direction: column; justify-content: center;
                page-break-inside: avoid;
            }
            .print-product { font-weight: 700; font-size: 9pt; line-height: 1; margin-bottom: 2mm; text-transform: uppercase; max-height: 18pt; overflow: hidden; }
            .print-date { font-size: 7.5pt; line-height: 1.1; font-weight: 600; }
            @page { size: 100mm 25mm; margin: 0; }
        }
    </style>
</head>
<body>

<!-- Top Navigation -->
<nav class="topnav">
    <div class="brand">
        <div class="brand-mark">TT</div>
        <div class="brand-text">
            <span class="brand-eyebrow">Tiny Togs</span>
            <span class="brand-title">Label Printing</span>
        </div>
    </div>
    <div class="topnav-spacer"></div>
    <a href="../" class="nav-btn nav-btn-ghost">
        <i class="fa-solid fa-house"></i> Portal
    </a>
    <button class="nav-btn nav-btn-solid" id="topPrintBtn">
        <i class="fa-solid fa-print"></i> Print Labels
    </button>
</nav>

<!-- Main Dashboard -->
<div class="dashboard">

    <!-- LEFT: Print Form -->
    <div class="left-panel">
        <div class="left-inner">

            <!-- Product Name -->
            <div class="section-card">
                <div class="section-card-header"><i class="fa-solid fa-tag"></i> Product</div>
                <div class="section-card-body">
                    <label class="field-label">Product name</label>
                    <div class="ac-wrap">
                        <input type="text" class="field-input" id="productName" autocomplete="off" placeholder="e.g. Aveeno Daily Moisturizing Lotion">
                        <div id="autocompleteList" class="ac-list"></div>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="section-card">
                <div class="section-card-header"><i class="fa-regular fa-calendar"></i> Dates</div>
                <div class="section-card-body split">
                    <!-- MFD -->
                    <div>
                        <label class="field-label">Manufacture date (MFD)</label>
                        <div class="date-dropdowns">
                            <div class="date-dropdown">
                                <div class="date-display" id="mfdYearDisp" data-type="year" data-prefix="mfd">
                                    <span>YYYY</span><i class="fa-solid fa-chevron-down dd-arrow"></i>
                                </div>
                                <div class="date-grid-popup"><div class="grid-container grid-years" id="mfdYearGrid"></div></div>
                            </div>
                            <div class="date-dropdown">
                                <div class="date-display" id="mfdMonthDisp" data-type="month" data-prefix="mfd">
                                    <span>MM</span><i class="fa-solid fa-chevron-down dd-arrow"></i>
                                </div>
                                <div class="date-grid-popup"><div class="grid-container grid-months" id="mfdMonthGrid"></div></div>
                            </div>
                            <div class="date-dropdown">
                                <div class="date-display" id="mfdDayDisp" data-type="day" data-prefix="mfd">
                                    <span>DD</span><i class="fa-solid fa-chevron-down dd-arrow"></i>
                                </div>
                                <div class="date-grid-popup"><div class="grid-container grid-days" id="mfdDayGrid"></div></div>
                            </div>
                        </div>
                        <input type="hidden" id="mfdDate">
                        <input type="hidden" id="mfdYearVal">
                        <input type="hidden" id="mfdMonthVal">
                        <input type="hidden" id="mfdDayVal">
                    </div>
                    <!-- EXP -->
                    <div>
                        <label class="field-label">Expiry date (EXP)</label>
                        <div class="date-dropdowns">
                            <div class="date-dropdown">
                                <div class="date-display" id="expYearDisp" data-type="year" data-prefix="exp">
                                    <span>YYYY</span><i class="fa-solid fa-chevron-down dd-arrow"></i>
                                </div>
                                <div class="date-grid-popup"><div class="grid-container grid-years" id="expYearGrid"></div></div>
                            </div>
                            <div class="date-dropdown">
                                <div class="date-display" id="expMonthDisp" data-type="month" data-prefix="exp">
                                    <span>MM</span><i class="fa-solid fa-chevron-down dd-arrow"></i>
                                </div>
                                <div class="date-grid-popup"><div class="grid-container grid-months" id="expMonthGrid"></div></div>
                            </div>
                            <div class="date-dropdown">
                                <div class="date-display" id="expDayDisp" data-type="day" data-prefix="exp">
                                    <span>DD</span><i class="fa-solid fa-chevron-down dd-arrow"></i>
                                </div>
                                <div class="date-grid-popup"><div class="grid-container grid-days" id="expDayGrid"></div></div>
                            </div>
                        </div>
                        <input type="hidden" id="expDate">
                        <input type="hidden" id="expYearVal">
                        <input type="hidden" id="expMonthVal">
                        <input type="hidden" id="expDayVal">
                        <div id="expSuggestions" class="pills"></div>
                    </div>
                </div>
            </div>

            <!-- Usage & Quantity -->
            <div class="section-card">
                <div class="section-card-header"><i class="fa-solid fa-box-open"></i> Usage &amp; quantity</div>
                <div class="section-card-body split">
                    <div>
                        <label class="field-label">Usable period after opening</label>
                        <input type="text" class="field-input" id="usablePeriod" placeholder="e.g. 12 Months">
                        <div class="pills" id="usePills">
                            <button class="pill use-btn" data-val="3 Months">3 Months</button>
                            <button class="pill use-btn" data-val="6 Months">6 Months</button>
                            <button class="pill use-btn" data-val="12 Months">12 Months</button>
                            <button class="pill use-btn" data-val="24 Months">24 Months</button>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Sticker count</label>
                        <div class="qty-row">
                            <button class="qty-btn" id="qtyMinus">−</button>
                            <input type="number" class="qty-input" id="stickerQty" value="1" min="1">
                            <button class="qty-btn" id="qtyPlus">+</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Button -->
            <div class="print-footer">
                <button class="print-btn" id="mainPrintBtn">
                    <i class="fa-solid fa-print"></i> Print Labels
                </button>
            </div>

        </div>
    </div>

    <!-- RIGHT: Live Preview + Batch Code Decoder -->
    <div class="right-panel">

        <div class="preview-stage">
            <div class="preview-eyebrow"><i class="fa-solid fa-eye"></i>Label preview</div>
            <div class="label-stage">
                <div class="sticker-preview">
                    <div class="sp-name" id="prevTitle">PRODUCT NAME</div>
                    <div class="sp-date" id="prevMfd">MFD: DD/MM/YYYY</div>
                    <div class="sp-date" id="prevExp">EXP: DD/MM/YYYY</div>
                    <div class="sp-date" id="prevUse">USE WITHIN: —</div>
                </div>
            </div>
        </div>

        <div class="decoder-wrap">

            <div>
                <div class="decoder-card-header"><i class="fa-solid fa-wand-magic-sparkles"></i> Batch code decoder</div>
                <label class="field-label">Batch / lot code</label>
                <div class="decoder-input-row">
                    <input type="text" id="batchCodeInput" class="decoder-input" placeholder="e.g. 103192">
                    <button class="decode-btn" id="decodeBtn"><i class="fa-solid fa-magnifying-glass"></i> Decode</button>
                </div>
            </div>

            <div class="decode-result" id="decodeResult">
                <div class="decode-result-label">Manufacture date</div>
                <div class="decode-result-date" id="decodedMfdText">—</div>
                <button class="apply-btn" id="applyMfdBtn">
                    <i class="fa-solid fa-arrow-left"></i> Apply as MFD
                </button>
            </div>

            <div class="decode-error" id="decodeError">
                ⚠ Could not recognize this batch code format.
            </div>

            <div class="info-box">
                <strong>How it works</strong><br>
                Aveeno uses Julian date format. For example, batch code <strong>103192</strong> means the <strong>103rd day</strong> of <strong>2019</strong>. This tool decodes DDDYY, YYDDD and DDDY formats automatically.
            </div>

            <div class="refbox">
                <div class="refbox-title">Quick reference</div>
                <div class="refbox-body">
                    <div><strong>DDDYY:</strong> 103<strong>19</strong> → 103rd day 2019</div>
                    <div><strong>DDDY:</strong> 103<strong>9</strong> → 103rd day 201<strong>9</strong></div>
                    <div><strong>YYDDD:</strong> <strong>19</strong>103 → 2019, 103rd day</div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Hidden print output -->
<div id="print-container"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // ─── DATE GRIDS SETUP ───
    const currYear = new Date().getFullYear();
    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

    let yearHtml = '';
    for (let y = currYear - 10; y <= currYear + 15; y++) yearHtml += `<div class="grid-btn" data-val="${y}">${y}</div>`;
    $('#mfdYearGrid, #expYearGrid').html(yearHtml);

    let monthHtml = '';
    for (let m = 1; m <= 12; m++) { let v = String(m).padStart(2,'0'); monthHtml += `<div class="grid-btn" data-val="${v}">${months[m-1]}</div>`; }
    $('#mfdMonthGrid, #expMonthGrid').html(monthHtml);

    let dayHtml = '';
    for (let d = 1; d <= 31; d++) { let v = String(d).padStart(2,'0'); dayHtml += `<div class="grid-btn" data-val="${v}">${v}</div>`; }
    $('#mfdDayGrid, #expDayGrid').html(dayHtml);

    // Toggle popups
    $('.date-display').on('click', function(e) {
        e.stopPropagation();
        let popup = $(this).siblings('.date-grid-popup');
        let isOpen = popup.hasClass('active');
        $('.date-grid-popup').removeClass('active');
        $('.date-display').removeClass('open');
        if (!isOpen) { popup.addClass('active'); $(this).addClass('open'); }
    });
    $(document).on('click', function() {
        $('.date-grid-popup').removeClass('active');
        $('.date-display').removeClass('open');
    });
    $('.date-grid-popup').on('click', e => e.stopPropagation());

    // Grid selection
    $(document).on('click', '.grid-btn', function() {
        let btn = $(this);
        let popup = btn.closest('.date-grid-popup');
        let display = popup.siblings('.date-display');
        let prefix = display.data('prefix');
        let type = display.data('type');
        let val = btn.data('val');

        btn.siblings().removeClass('selected');
        btn.addClass('selected');
        display.find('span').text(type === 'month' ? months[parseInt(val)-1] : val);
        popup.removeClass('active');
        display.removeClass('open');

        $(`#${prefix}${type.charAt(0).toUpperCase()+type.slice(1)}Val`).val(val);
        updateHiddenDate(prefix);
    });

    function updateHiddenDate(prefix) {
        let y = $(`#${prefix}YearVal`).val();
        let m = $(`#${prefix}MonthVal`).val();
        let d = $(`#${prefix}DayVal`).val();
        let val = (y && m && d) ? `${y}-${m}-${d}` : '';
        $(`#${prefix}Date`).val(val).trigger('change');
    }

    function syncToDate(prefix, dateStr) {
        if (!dateStr) {
            $(`#${prefix}YearDisp span`).text('YYYY');
            $(`#${prefix}MonthDisp span`).text('MM');
            $(`#${prefix}DayDisp span`).text('DD');
            $(`#${prefix}YearVal, #${prefix}MonthVal, #${prefix}DayVal`).val('');
            $(`#${prefix}YearGrid .grid-btn, #${prefix}MonthGrid .grid-btn, #${prefix}DayGrid .grid-btn`).removeClass('selected');
            return;
        }
        let [y, m, d] = dateStr.split('-');
        $(`#${prefix}YearVal`).val(y); $(`#${prefix}MonthVal`).val(m); $(`#${prefix}DayVal`).val(d);
        $(`#${prefix}YearDisp span`).text(y);
        $(`#${prefix}MonthDisp span`).text(months[parseInt(m)-1]);
        $(`#${prefix}DayDisp span`).text(d);
        $(`#${prefix}YearGrid .grid-btn`).removeClass('selected').filter(`[data-val="${y}"]`).addClass('selected');
        $(`#${prefix}MonthGrid .grid-btn`).removeClass('selected').filter(`[data-val="${m}"]`).addClass('selected');
        $(`#${prefix}DayGrid .grid-btn`).removeClass('selected').filter(`[data-val="${d}"]`).addClass('selected');
    }

    // ─── LIVE PREVIEW ───
    function updatePreview() {
        $('#prevTitle').text($('#productName').val() || 'PRODUCT NAME');
        let mfd = $('#mfdDate').val();
        $('#prevMfd').text('MFD: ' + (mfd ? fmtDate(mfd) : 'DD/MM/YYYY'));
        let exp = $('#expDate').val();
        $('#prevExp').text('EXP: ' + (exp ? fmtDate(exp) : 'DD/MM/YYYY'));
        $('#prevUse').text('USE WITHIN: ' + ($('#usablePeriod').val() || '—'));
    }
    $('#productName, #usablePeriod').on('input', updatePreview);
    $('#mfdDate, #expDate').on('change', updatePreview);

    function fmtDate(s) {
        let p = s.split('-');
        return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : s;
    }

    // ─── MFD → EXP SUGGESTIONS ───
    $('#mfdDate').on('change', function() {
        let mfd = $(this).val();
        $('#expSuggestions').empty();
        if (!mfd) return;
        let date = new Date(mfd);
        for (let i = 1; i <= 3; i++) {
            let ny = new Date(date);
            ny.setFullYear(date.getFullYear() + i);
            let ds = ny.getFullYear() + '-' + String(ny.getMonth()+1).padStart(2,'0') + '-' + String(ny.getDate()).padStart(2,'0');
            $('#expSuggestions').append(`<button class="pill exp-btn" data-date="${ds}">+${i}Yr</button>`);
        }
    });
    $(document).on('click', '.exp-btn', function() {
        let d = $(this).data('date');
        syncToDate('exp', d);
        $('#expDate').val(d).trigger('change');
    });

    // ─── USE PERIOD PILLS ───
    $(document).on('click', '.use-btn', function() {
        $('#usablePeriod').val($(this).data('val')).trigger('input');
    });

    // ─── AUTOCOMPLETE ───
    let acTimer;
    $('#productName').on('keyup', function() {
        clearTimeout(acTimer);
        let val = $(this).val();
        if (val.length < 2) { $('#autocompleteList').hide(); return; }
        acTimer = setTimeout(() => {
            $.getJSON('index.php?action=search_product', { term: val }, function(data) {
                $('#autocompleteList').empty();
                if (data.length) {
                    data.forEach(item => $('#autocompleteList').append(`<div class="ac-item">${item}</div>`));
                    $('#autocompleteList').show();
                } else { $('#autocompleteList').hide(); }
            });
        }, 280);
    });
    $(document).on('click', '.ac-item', function() {
        $('#productName').val($(this).text()).trigger('input');
        $('#autocompleteList').hide();
    });
    $(document).on('click', e => { if (!$(e.target).closest('.ac-wrap').length) $('#autocompleteList').hide(); });

    // ─── QTY STEPPER ───
    $('#qtyMinus').on('click', function() {
        let v = parseInt($('#stickerQty').val()) || 1;
        if (v > 1) $('#stickerQty').val(v - 1);
    });
    $('#qtyPlus').on('click', function() {
        let v = parseInt($('#stickerQty').val()) || 1;
        $('#stickerQty').val(v + 1);
    });

    // ─── PRINT ───
    function doPrint() {
        let mfd = $('#mfdDate').val();
        let exp = $('#expDate').val();
        let name = $('#productName').val().trim();
        let qty = parseInt($('#stickerQty').val()) || 1;
        let use = $('#usablePeriod').val();

        if (!name) { alert('Please enter a product name.'); return; }
        if (!mfd)  { alert('Please select a Manufacture Date.'); return; }
        if (!exp)  { alert('Please select an Expiry Date.'); return; }
        if (!use)  { alert('Please enter a usable period.'); return; }
        if (exp < mfd) { alert('Expiry Date cannot be earlier than Manufacture Date.'); return; }

        $.post('index.php', { action: 'save_product', product_name: name });

        let container = $('#print-container').empty();
        for (let i = 0; i < qty; i++) {
            container.append(`
                <div class="print-label">
                    <div class="print-product">${name}</div>
                    <div class="print-date">MFD: ${fmtDate(mfd)}</div>
                    <div class="print-date">EXP: ${fmtDate(exp)}</div>
                    <div class="print-date">USE WITHIN: ${use}</div>
                </div>`);
        }
        setTimeout(() => window.print(), 80);
    }
    $('#mainPrintBtn, #topPrintBtn').on('click', doPrint);

    // ─── BATCH CODE DECODER ───
    $('#decodeBtn').on('click', function() {
        let code = $('#batchCodeInput').val().toUpperCase();
        let digits = code.replace(/\D/g, '');
        let currY = new Date().getFullYear();
        let candidates = [];

        $('#decodeError').removeClass('show');
        $('#decodeResult').removeClass('show');

        if (digits.length >= 4) {
            let ddd1 = parseInt(digits.substring(0, 3));
            let yy12 = digits.length >= 5 ? parseInt(digits.substring(3, 5)) : null;
            let y11  = parseInt(digits.substring(3, 4));
            let yy2  = digits.length >= 5 ? parseInt(digits.substring(0, 2)) : null;
            let ddd2 = digits.length >= 5 ? parseInt(digits.substring(2, 5)) : null;

            if (ddd1 >= 1 && ddd1 <= 366 && yy12 !== null) {
                let d = new Date(2000 + yy12, 0); d.setDate(ddd1);
                candidates.push({ date: d, fmt: 'DDDYY' });
            }
            if (ddd2 >= 1 && ddd2 <= 366 && yy2 !== null) {
                let d = new Date(2000 + yy2, 0); d.setDate(ddd2);
                candidates.push({ date: d, fmt: 'YYDDD' });
            }
            if (ddd1 >= 1 && ddd1 <= 366) {
                let py = Math.floor(currY / 10) * 10 + y11;
                if (py > currY + 1) py -= 10;
                let d = new Date(py, 0); d.setDate(ddd1);
                candidates.push({ date: d, fmt: 'DDDY' });
            }
        }

        let mfdDate = null;
        if (candidates.length) {
            let best = null, minScore = 9999;
            candidates.forEach(c => {
                let score = Math.abs(c.date.getFullYear() - currY);
                if (c.date.getFullYear() > currY + 1) score += 20;
                if (c.fmt === 'DDDY' && digits.length >= 5) score += 50;
                if (c.fmt === 'YYDDD') score += 1;
                if (score < minScore) { minScore = score; best = c.date; }
            });
            mfdDate = best;
        }

        if (mfdDate && !isNaN(mfdDate.getTime())) {
            let iso = mfdDate.getFullYear() + '-' + String(mfdDate.getMonth()+1).padStart(2,'0') + '-' + String(mfdDate.getDate()).padStart(2,'0');
            let display = mfdDate.toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' });
            $('#decodedMfdText').text(display);
            $('#applyMfdBtn').data('date', iso);
            $('#decodeResult').addClass('show');
        } else {
            $('#decodeError').addClass('show');
        }
    });

    // Allow Enter key to trigger decode
    $('#batchCodeInput').on('keydown', function(e) {
        if (e.key === 'Enter') $('#decodeBtn').click();
    });

    $('#applyMfdBtn').on('click', function() {
        let iso = $(this).data('date');
        syncToDate('mfd', iso);
        $('#mfdDate').val(iso).trigger('change');
        $('#batchCodeInput').val('');
        $('#decodeResult').removeClass('show');
    });

});
</script>
</body>
</html>