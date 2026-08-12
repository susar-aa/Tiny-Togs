<?php
$host = 'localhost';
$dbname = 'tiny_togs';
$username = 'suzxlabs';
$password = 'Susara@200611003614';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #007aff;
            --blue-light: #e8f3ff;
            --blue-dark: #0063d1;
            --green: #34c759;
            --green-light: #e6f9ed;
            --orange: #ff9500;
            --orange-light: #fff3e0;
            --purple: #af52de;
            --purple-light: #f3eafb;
            --bg: #f2f2f7;
            --card: #ffffff;
            --label: #1c1c1e;
            --secondary: #6b6b70;
            --tertiary: #aeaeb2;
            --sep: #e5e5ea;
            --sep2: #f2f2f7;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow: 0 1px 8px rgba(0,0,0,0.06), 0 4px 20px rgba(0,0,0,0.04);
            --font: -apple-system, BlinkMacSystemFont, "Inter", "SF Pro Display", "Segoe UI", sans-serif;
        }

        html, body { height: 100%; overflow: hidden; }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--label);
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP NAV ── */
        .topnav {
            height: 52px;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--sep);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            gap: 0.75rem;
            flex-shrink: 0;
            z-index: 100;
        }
        .topnav-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--blue), #5ac8fa);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.9rem;
        }
        .topnav-title { font-weight: 700; font-size: 1rem; letter-spacing: -0.01em; }
        .topnav-sub { font-size: 0.78rem; color: var(--secondary); margin-left: 2px; }
        .topnav-spacer { flex: 1; }
        .nav-btn {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.4rem 0.85rem; border-radius: 980px;
            font-size: 0.8rem; font-weight: 600; cursor: pointer;
            border: none; text-decoration: none; transition: 0.18s ease;
        }
        .nav-btn-ghost { background: var(--sep2); color: var(--label); }
        .nav-btn-ghost:hover { background: var(--sep); }
        .nav-btn-blue { background: var(--blue); color: #fff; box-shadow: 0 3px 10px rgba(0,122,255,0.28); }
        .nav-btn-blue:hover { background: var(--blue-dark); color: #fff; }

        /* ── MAIN LAYOUT ── */
        .dashboard {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 0;
            overflow: hidden;
            height: calc(100vh - 52px);
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            overflow-y: auto;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .left-panel::-webkit-scrollbar { width: 5px; }
        .left-panel::-webkit-scrollbar-track { background: transparent; }
        .left-panel::-webkit-scrollbar-thumb { background: var(--sep); border-radius: 3px; }

        /* ── RIGHT PANEL ── */
        .right-panel {
            border-left: 1px solid var(--sep);
            background: #fff;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .right-panel::-webkit-scrollbar { width: 4px; }
        .right-panel::-webkit-scrollbar-track { background: transparent; }
        .right-panel::-webkit-scrollbar-thumb { background: var(--sep); border-radius: 3px; }
        .right-panel-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--sep);
            display: flex; align-items: center; gap: 0.6rem;
            font-weight: 700; font-size: 0.9rem;
            flex-shrink: 0;
            background: var(--bg);
        }
        .right-panel-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; gap: 1rem; }

        /* ── SECTION CARD ── */
        .section-card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: visible;
        }
        .section-card-header {
            padding: 0.9rem 1.1rem 0;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--secondary);
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .section-card-body { padding: 0.75rem 1.1rem 1.1rem; }

        /* ── FORM ELEMENTS ── */
        label.field-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.4rem;
            letter-spacing: 0.02em;
        }
        .field-input {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1.5px solid var(--sep);
            border-radius: var(--radius-sm);
            font-family: var(--font);
            font-size: 0.9rem;
            color: var(--label);
            background: #fff;
            outline: none;
            transition: border-color 0.18s;
        }
        .field-input:focus { border-color: var(--blue); }
        .field-input::placeholder { color: var(--tertiary); }

        /* ── DATE GRID ── */
        .date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .date-dropdowns { display: flex; gap: 0.5rem; }
        .date-dropdown { position: relative; flex: 1; }
        .date-display {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.6rem 0.7rem;
            border: 1.5px solid var(--sep);
            border-radius: var(--radius-sm);
            background: #fff; cursor: pointer;
            font-size: 0.85rem; font-weight: 500; color: var(--label);
            transition: border-color 0.18s;
            user-select: none;
        }
        .date-display:hover, .date-display.open { border-color: var(--blue); }
        .date-display .dd-arrow { font-size: 0.65rem; color: var(--tertiary); transition: transform 0.2s; }
        .date-display.open .dd-arrow { transform: rotate(180deg); }
        .date-grid-popup {
            display: none;
            position: absolute; top: calc(100% + 4px); left: 0;
            z-index: 500; background: #fff;
            border: 1px solid var(--sep);
            border-radius: 12px;
            padding: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.14);
        }
        .date-grid-popup.active { display: block; }
        .grid-container { display: grid; gap: 4px; }
        .grid-years { grid-template-columns: repeat(4, 1fr); width: 248px; }
        .grid-months { grid-template-columns: repeat(4, 1fr); width: 210px; }
        .grid-days { grid-template-columns: repeat(7, 1fr); width: 248px; }
        .grid-btn {
            border: none; background: var(--sep2);
            border-radius: 6px; padding: 7px 0;
            text-align: center; cursor: pointer;
            font-size: 0.8rem; color: var(--label);
            transition: 0.15s; font-family: var(--font);
        }
        .grid-btn:hover { background: var(--sep); }
        .grid-btn.selected { background: var(--blue); color: #fff; font-weight: 700; }

        /* ── SUGGESTION PILLS ── */
        .pills { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.5rem; }
        .pill {
            padding: 0.3rem 0.65rem;
            border-radius: 980px; border: none;
            background: var(--blue-light); color: var(--blue);
            font-size: 0.78rem; font-weight: 600; cursor: pointer;
            transition: 0.15s; font-family: var(--font);
        }
        .pill:hover { background: #d0e8ff; }

        /* ── AUTOCOMPLETE ── */
        .ac-wrap { position: relative; }
        .ac-list {
            position: absolute; z-index: 400; width: 100%;
            background: #fff; border: 1px solid var(--sep);
            border-radius: 10px; box-shadow: 0 8px 28px rgba(0,0,0,0.1);
            max-height: 180px; overflow-y: auto; display: none; top: calc(100% + 4px);
        }
        .ac-item {
            padding: 0.6rem 0.85rem; cursor: pointer;
            font-size: 0.88rem; color: var(--label);
            border-bottom: 1px solid var(--sep2);
        }
        .ac-item:hover { background: var(--sep2); }
        .ac-item:last-child { border-bottom: none; }

        /* ── STICKER PREVIEW ── */
        .preview-wrap { display: flex; align-items: center; justify-content: center; padding: 0.75rem 0; }
        .sticker-preview {
            width: 220px; height: 110px;
            background: #fff;
            border: 2px dashed #c0cfe8;
            border-radius: 6px;
            padding: 10px 12px;
            font-family: Arial, sans-serif;
            display: flex; flex-direction: column; justify-content: center;
            box-shadow: 0 2px 12px rgba(0,122,255,0.08);
        }
        .sticker-preview .sp-name {
            font-size: 11px; font-weight: 800; line-height: 1.1;
            text-transform: uppercase; margin-bottom: 6px; color: #111;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .sticker-preview .sp-date { font-size: 9px; font-weight: 700; line-height: 1.4; color: #222; }

        /* ── PRINT BUTTON ── */
        .print-btn {
            width: 100%; padding: 0.85rem;
            background: var(--blue); color: #fff;
            border: none; border-radius: 12px;
            font-family: var(--font); font-size: 0.95rem; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            gap: 0.5rem; box-shadow: 0 4px 16px rgba(0,122,255,0.3);
            transition: 0.2s;
        }
        .print-btn:hover { background: var(--blue-dark); transform: translateY(-1px); }
        .print-btn:active { transform: translateY(0); }

        /* ── DECODER PANEL ── */
        .decoder-input-row { display: flex; gap: 0.5rem; }
        .decoder-input { flex: 1; padding: 0.6rem 0.8rem; border: 1.5px solid var(--sep); border-radius: var(--radius-sm); font-size: 1rem; font-family: var(--font); outline: none; transition: border-color 0.18s; letter-spacing: 0.08em; font-weight: 600; }
        .decoder-input:focus { border-color: var(--blue); }
        .decode-btn {
            padding: 0.6rem 1rem; background: var(--blue); color: #fff;
            border: none; border-radius: var(--radius-sm); font-family: var(--font);
            font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: 0.18s;
        }
        .decode-btn:hover { background: var(--blue-dark); }

        .decode-result {
            background: var(--green-light); border-radius: 10px;
            padding: 0.85rem; display: none;
        }
        .decode-result.show { display: block; }
        .decode-result-label { font-size: 0.72rem; color: var(--secondary); font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 0.2rem; }
        .decode-result-date { font-size: 1.3rem; font-weight: 800; color: var(--label); letter-spacing: -0.02em; }
        .apply-btn {
            width: 100%; margin-top: 0.6rem; padding: 0.55rem;
            background: var(--green); color: #fff;
            border: none; border-radius: 8px; font-family: var(--font);
            font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: 0.18s;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }
        .apply-btn:hover { background: #29a648; }

        .decode-error { display: none; color: #ff3b30; font-size: 0.82rem; font-weight: 600; text-align: center; padding: 0.5rem 0; }
        .decode-error.show { display: block; }

        .info-box {
            background: var(--blue-light); border-radius: 10px;
            padding: 0.85rem; font-size: 0.8rem; color: var(--secondary); line-height: 1.5;
        }
        .info-box strong { color: var(--blue); }

        .qty-row { display: flex; align-items: center; gap: 0.75rem; }
        .qty-btn {
            width: 36px; height: 36px; border: none;
            background: var(--sep2); border-radius: 50%;
            font-size: 1.2rem; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--blue); transition: 0.15s; flex-shrink: 0;
        }
        .qty-btn:hover { background: var(--blue-light); }
        .qty-input {
            width: 64px; text-align: center;
            border: 1.5px solid var(--sep); border-radius: var(--radius-sm);
            padding: 0.45rem; font-size: 1.1rem; font-weight: 700;
            font-family: var(--font); outline: none;
        }
        .qty-input:focus { border-color: var(--blue); }

        /* ── @MEDIA PRINT ── */
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
    <div class="topnav-icon"><i class="fa-solid fa-tags"></i></div>
    <div>
        <div class="topnav-title">Label Printing</div>
    </div>
    <div class="topnav-sub">Tiny Togs</div>
    <div class="topnav-spacer"></div>
    <a href="../" class="nav-btn nav-btn-ghost">
        <i class="fa-solid fa-house"></i> Portal
    </a>
    <button class="nav-btn nav-btn-blue" id="topPrintBtn">
        <i class="fa-solid fa-print"></i> Print Labels
    </button>
</nav>

<!-- Main Dashboard -->
<div class="dashboard">

    <!-- LEFT: Print Form -->
    <div class="left-panel">

        <!-- Product Name -->
        <div class="section-card">
            <div class="section-card-header">Product</div>
            <div class="section-card-body">
                <label class="field-label">Product Name</label>
                <div class="ac-wrap">
                    <input type="text" class="field-input" id="productName" autocomplete="off" placeholder="e.g. Aveeno Daily Moisturizing Lotion">
                    <div id="autocompleteList" class="ac-list"></div>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="section-card">
            <div class="section-card-header">Dates</div>
            <div class="section-card-body">
                <div class="date-row">
                    <!-- MFD -->
                    <div>
                        <label class="field-label">Manufacture Date (MFD)</label>
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
                        <label class="field-label">Expiry Date (EXP)</label>
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
        </div>

        <!-- Usable Period -->
        <div class="section-card">
            <div class="section-card-header">Usable Period After Opening</div>
            <div class="section-card-body">
                <input type="text" class="field-input" id="usablePeriod" placeholder="e.g. 12 Months">
                <div class="pills" id="usePills">
                    <button class="pill use-btn" data-val="3 Months">3 Months</button>
                    <button class="pill use-btn" data-val="6 Months">6 Months</button>
                    <button class="pill use-btn" data-val="12 Months">12 Months</button>
                    <button class="pill use-btn" data-val="24 Months">24 Months</button>
                </div>
            </div>
        </div>

        <!-- Qty + Print Preview row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">

            <!-- Qty -->
            <div class="section-card">
                <div class="section-card-header">Sticker Count</div>
                <div class="section-card-body">
                    <div class="qty-row">
                        <button class="qty-btn" id="qtyMinus">−</button>
                        <input type="number" class="qty-input" id="stickerQty" value="1" min="1">
                        <button class="qty-btn" id="qtyPlus">+</button>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="section-card">
                <div class="section-card-header">Label Preview</div>
                <div class="section-card-body p-0">
                    <div class="preview-wrap">
                        <div class="sticker-preview">
                            <div class="sp-name" id="prevTitle">PRODUCT NAME</div>
                            <div class="sp-date" id="prevMfd">MFD: DD/MM/YYYY</div>
                            <div class="sp-date" id="prevExp">EXP: DD/MM/YYYY</div>
                            <div class="sp-date" id="prevUse">USE WITHIN: —</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Print Button -->
        <button class="print-btn" id="mainPrintBtn">
            <i class="fa-solid fa-print"></i> Print Labels
        </button>

    </div>

    <!-- RIGHT: Batch Code Decoder -->
    <div class="right-panel">
        <div class="right-panel-header">
            <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--blue);"></i>
            Batch Code Decoder
        </div>
        <div class="right-panel-body">

            <div>
                <label class="field-label">Enter Batch / Lot Code</label>
                <div class="decoder-input-row">
                    <input type="text" id="batchCodeInput" class="decoder-input" placeholder="e.g. 103192">
                    <button class="decode-btn" id="decodeBtn"><i class="fa-solid fa-magnifying-glass"></i> Decode</button>
                </div>
            </div>

            <div class="decode-result" id="decodeResult">
                <div class="decode-result-label">Manufacture Date</div>
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

            <div style="flex: 1;"></div>

            <div style="background: var(--sep2); border-radius: 10px; padding: 0.85rem;">
                <div style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; color: var(--tertiary); letter-spacing: 0.06em; margin-bottom: 0.5rem;">Quick Reference</div>
                <div style="font-size: 0.8rem; color: var(--secondary); line-height: 1.6;">
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
