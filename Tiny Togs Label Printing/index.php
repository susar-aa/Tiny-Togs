<?php
// TEMPORARY DEBUGGING: These lines will force the server to show you the actual error instead of a blank 500 page.
// You can remove or comment these out once the page loads successfully.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'tiny_togs';
$username = 'suzxlabs';
$password = 'Susara@200611003614';

// Check if PDO is installed (A missing PDO extension is a common cause of 500 errors on Windows/IIS)
if (!class_exists('PDO')) {
    die("<h1>Server Error</h1><p>The PHP 'PDO' extension is missing or disabled on this server. Please enable it in your php.ini file.</p>");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("<h1>Database Connection Failed</h1><p>Please check your database name, username, and password.</p>");
}

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    
    if ($action === 'search_product') {
        // Replaced ?? with isset() ternary for compatibility with older PHP versions (PHP 5.x)
        $term = isset($_GET['term']) ? $_GET['term'] : '';
        $stmt = $pdo->prepare("SELECT product_name FROM label_products WHERE product_name LIKE :term ORDER BY product_name ASC LIMIT 10");
        $stmt->execute(['term' => '%' . $term . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
    
    if ($action === 'save_product') {
        $name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
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
    <title>Label Operations Studio | Tiny Togs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0F172A;       
            --primary-hover: #1E293B; 
            --accent: #3B82F6;        
            --accent-hover: #2563EB;  
            --accent-light: #EFF6FF;  
            
            --success: #10B981;
            --success-light: #D1FAE5;
            --success-dark: #059669;
            --error: #EF4444;

            --bg-body: #F8FAFC;       
            --bg-surface: #FFFFFF;
            --bg-panel: #F1F5F9;      
            
            --text-main: #0F172A;
            --text-muted: #64748B;
            --text-light: #94A3B8;

            --border-color: #E2E8F0;
            --border-focus: #93C5FD;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-focus: 0 0 0 3px rgba(59, 130, 246, 0.15);

            --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-body);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
        }

        .header {
            height: 64px; background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; flex-shrink: 0; z-index: 100; box-shadow: var(--shadow-sm);
        }
        .header-brand { display: flex; align-items: center; gap: 0.75rem; }
        .brand-icon {
            width: 36px; height: 36px; background: var(--primary); color: white;
            border-radius: var(--radius-sm); display: flex; align-items: center;
            justify-content: center; font-size: 1.1rem; box-shadow: 0 2px 4px rgba(15, 23, 42, 0.2);
        }
        .brand-text { display: flex; flex-direction: column; line-height: 1.2; }
        .brand-title { font-weight: 700; font-size: 1.05rem; color: var(--text-main); letter-spacing: -0.01em; }
        .brand-subtitle { font-size: 0.75rem; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        
        .header-actions { display: flex; align-items: center; gap: 1rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.5rem 1rem; border-radius: var(--radius-sm); font-size: 0.875rem;
            font-weight: 600; cursor: pointer; border: 1px solid transparent;
            transition: all var(--transition-fast); text-decoration: none; font-family: inherit;
        }
        .btn-outline { background: transparent; border-color: var(--border-color); color: var(--text-main); }
        .btn-outline:hover { background: var(--bg-panel); border-color: #CBD5E1; }
        .btn-primary { background: var(--primary); color: white; box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: var(--shadow-md); }

        .workspace {
            flex: 1; display: grid; grid-template-columns: minmax(0, 1fr) 350px;
            overflow: hidden; height: calc(100vh - 64px);
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

        .main-panel { overflow-y: auto; padding: 1.5rem; background: var(--bg-body); }
        .form-container { max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 1rem; }
        .section-header { font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem; letter-spacing: -0.01em; }
        .section-desc { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; }

        .card {
            background: var(--bg-surface); border: 1px solid var(--border-color);
            border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
            overflow: visible; transition: box-shadow var(--transition-fast);
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-body { padding: 1rem 1.25rem; }

        .form-group { margin-bottom: 0.75rem; }
        .label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem; }
        .label-hint { font-weight: 400; color: var(--text-muted); font-size: 0.75rem; margin-left: 0.25rem; }
        .input {
            width: 100%; padding: 0.6rem 0.875rem; border: 1px solid var(--border-color);
            border-radius: var(--radius-sm); font-family: inherit; font-size: 0.9rem;
            color: var(--text-main); background: var(--bg-surface); transition: all var(--transition-fast);
        }
        .input:focus { outline: none; border-color: var(--accent); box-shadow: var(--shadow-focus); }
        .input::placeholder { color: var(--text-light); }

        .date-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .date-selectors { display: flex; gap: 0.4rem; }
        .date-dropdown { position: relative; flex: 1; }
        
        .date-trigger {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.6rem; border: 1px solid var(--border-color);
            border-radius: var(--radius-sm); background: var(--bg-surface);
            cursor: pointer; font-size: 0.85rem; font-weight: 500;
            color: var(--text-main); transition: all var(--transition-fast); user-select: none;
        }
        .date-trigger:hover, .date-trigger.open { border-color: var(--accent); }
        .date-trigger.open { box-shadow: var(--shadow-focus); }
        .date-trigger i { font-size: 0.7rem; color: var(--text-muted); transition: transform 0.2s ease; }
        .date-trigger.open i { transform: rotate(180deg); }

        .date-popup {
            display: none; position: absolute; top: calc(100% + 4px); left: 0; z-index: 50;
            background: var(--bg-surface); border: 1px solid var(--border-color);
            border-radius: var(--radius-md); padding: 0.5rem; box-shadow: var(--shadow-lg);
            animation: fadeInDown 0.2s ease-out forwards;
        }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .date-popup.active { display: block; }
        
        .grid-layout { display: grid; gap: 2px; }
        .grid-years { grid-template-columns: repeat(4, 1fr); width: 220px; }
        .grid-months { grid-template-columns: repeat(4, 1fr); width: 200px; }
        .grid-days { grid-template-columns: repeat(7, 1fr); width: 220px; }
        .grid-item {
            border: none; background: transparent; border-radius: var(--radius-sm);
            padding: 0.4rem 0; text-align: center; cursor: pointer; font-size: 0.85rem;
            color: var(--text-main); font-weight: 500; transition: all var(--transition-fast);
        }
        .grid-item:hover { background: var(--bg-panel); }
        .grid-item.selected { background: var(--accent); color: white; font-weight: 600; }

        .pill-group { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.5rem; }
        .pill {
            padding: 0.25rem 0.6rem; border-radius: 99px; border: 1px solid var(--border-color);
            background: var(--bg-surface); color: var(--text-muted); font-size: 0.75rem;
            font-weight: 600; cursor: pointer; transition: all var(--transition-fast);
        }
        .pill:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }

        .ac-wrapper { position: relative; }
        .ac-dropdown {
            position: absolute; z-index: 40; width: 100%; background: var(--bg-surface);
            border: 1px solid var(--border-color); border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg); max-height: 200px; overflow-y: auto;
            display: none; top: calc(100% + 4px);
        }
        .ac-option { padding: 0.75rem 1rem; cursor: pointer; font-size: 0.9rem; color: var(--text-main); transition: background var(--transition-fast); }
        .ac-option:hover { background: var(--bg-panel); }
        .ac-option:not(:last-child) { border-bottom: 1px solid var(--bg-panel); }

        .side-panel { background: var(--bg-surface); border-left: 1px solid var(--border-color); display: flex; flex-direction: column; }
        .side-header {
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex;
            align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.95rem; color: var(--text-main);
        }
        .side-header i { color: var(--accent); font-size: 1rem; }
        .side-body { padding: 1.25rem; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; }

        .preview-container {
            background: var(--bg-panel); border-radius: var(--radius-md); padding: 1rem;
            display: flex; flex-direction: column; align-items: center; border: 1px dashed var(--border-color);
        }
        .preview-label {
            font-size: 0.7rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; align-self: flex-start;
        }
        .sticker-canvas {
            width: 180px; height: 90px; background: #fff; border-radius: 4px; padding: 10px 12px;
            font-family: Arial, sans-serif; display: flex; flex-direction: column; justify-content: center;
            box-shadow: var(--shadow-sm); border: 1px solid #E2E8F0;
        }
        .sticker-canvas .sc-name {
            font-size: 10px; font-weight: 800; line-height: 1.2; text-transform: uppercase;
            margin-bottom: 6px; color: #000; display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .sticker-canvas .sc-meta { font-size: 8px; font-weight: 700; line-height: 1.4; color: #111; }

        .decoder-widget { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; }
        .decoder-input-group { display: flex; gap: 0.5rem; margin-top: 0.25rem; }
        .decoder-input-group .input { font-family: monospace; font-size: 1rem; letter-spacing: 0.05em; text-transform: uppercase; padding: 0.5rem 0.75rem; }
        
        .result-card {
            margin-top: 0.75rem; background: var(--success-light); border: 1px solid #A7F3D0;
            border-radius: var(--radius-sm); padding: 0.75rem; display: none; animation: fadeInDown 0.2s ease-out;
        }
        .result-card.active { display: block; }
        .result-title { font-size: 0.65rem; color: var(--success-dark); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .result-value { font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0.15rem 0 0.5rem 0; }
        .btn-apply {
            width: 100%; background: var(--success); color: white; border: none; padding: 0.4rem;
            border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex;
            align-items: center; justify-content: center; gap: 0.4rem; transition: background var(--transition-fast);
        }
        .btn-apply:hover { background: var(--success-dark); }
        .error-msg {
            display: none; margin-top: 0.75rem; color: var(--error); font-size: 0.8rem; font-weight: 500;
            padding: 0.5rem; background: #FEF2F2; border-radius: var(--radius-sm); border: 1px solid #FECACA;
        }
        .error-msg.active { display: block; }
        .info-panel {
            margin-top: 1rem; padding: 0.75rem; background: var(--accent-light); border-radius: var(--radius-sm);
            border: 1px solid #DBEAFE; font-size: 0.75rem; color: #1E3A8A; line-height: 1.5;
        }

        .action-row { display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; align-items: end; margin-top: 0.5rem; }
        .qty-control {
            display: flex; align-items: center; border: 1px solid var(--border-color);
            border-radius: var(--radius-sm); background: var(--bg-surface); overflow: hidden;
            height: 40px;
        }
        .qty-btn {
            background: transparent; border: none; padding: 0 0.75rem; color: var(--text-muted);
            cursor: pointer; transition: background var(--transition-fast), color var(--transition-fast);
            height: 100%;
        }
        .qty-btn:hover { background: var(--bg-panel); color: var(--text-main); }
        .qty-input {
            flex: 1; text-align: center; border: none; border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color); padding: 0; font-size: 0.95rem;
            font-weight: 600; color: var(--text-main); width: 50px; height: 100%; -moz-appearance: textfield;
        }
        .qty-input::-webkit-outer-spin-button, .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .btn-print-large { width: 100%; padding: 0.75rem; font-size: 0.95rem; display: flex; justify-content: center; height: 40px; align-items: center; }

        #print-container { display: none; }

        @media print {
            body * { visibility: hidden; }
            #print-container, #print-container * { visibility: visible; }
            #print-container {
                display: block;
                position: absolute; left: 0; top: 0; width: 100mm; margin: 0; padding: 0;
            }
            .print-row {
                width: 100mm; 
                height: 25mm; 
                display: flex; 
                flex-direction: row; 
                page-break-after: always;
                page-break-inside: avoid;
                break-after: page;
                break-inside: avoid;
                overflow: hidden;
            }
            .print-label {
                width: 50mm; height: 25mm; box-sizing: border-box; padding: 2mm 3mm; overflow: hidden;
                font-family: Arial, sans-serif; color: #000; background: #fff;
                display: flex; flex-direction: column; justify-content: center;
            }
            .print-product { font-weight: 700; font-size: 9pt; line-height: 1; margin-bottom: 2mm; text-transform: uppercase; max-height: 18pt; overflow: hidden; }
            .print-date { font-size: 7.5pt; line-height: 1.1; font-weight: 600; }
            @page { size: 100mm 25mm; margin: 0; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-brand">
        <div class="brand-icon"><i class="fa-solid fa-tags"></i></div>
        <div class="brand-text">
            <span class="brand-title">Label Operations</span>
            <span class="brand-subtitle">Tiny Togs</span>
        </div>
    </div>
    <div class="header-actions">
        <a href="../" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Portal
        </a>
        <button class="btn btn-primary" id="topPrintBtn">
            <i class="fa-solid fa-print"></i> Print Now
        </button>
    </div>
</header>

<div class="workspace">
    <main class="main-panel">
        <div class="form-container">
            
            <div>
                <h1 class="section-header">Configure Print Batch</h1>
                <p class="section-desc">Set up product details and dates for your sticker labels.</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label class="label">Product Name</label>
                        <div class="ac-wrapper">
                            <input type="text" class="input" id="productName" autocomplete="off" placeholder="e.g., Aveeno Daily Moisturizing Lotion">
                            <div id="autocompleteList" class="ac-dropdown"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="date-columns">
                        
                        <div>
                            <label class="label">Manufacture Date <span class="label-hint">(MFD)</span></label>
                            <div class="date-selectors">
                                <div class="date-dropdown">
                                    <div class="date-trigger" id="mfdYearDisp" data-type="year" data-prefix="mfd">
                                        <span>YYYY</span><i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="date-popup"><div class="grid-layout grid-years" id="mfdYearGrid"></div></div>
                                </div>
                                <div class="date-dropdown">
                                    <div class="date-trigger" id="mfdMonthDisp" data-type="month" data-prefix="mfd">
                                        <span>MM</span><i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="date-popup"><div class="grid-layout grid-months" id="mfdMonthGrid"></div></div>
                                </div>
                                <div class="date-dropdown">
                                    <div class="date-trigger" id="mfdDayDisp" data-type="day" data-prefix="mfd">
                                        <span>DD</span><i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="date-popup"><div class="grid-layout grid-days" id="mfdDayGrid"></div></div>
                                </div>
                            </div>
                            <input type="hidden" id="mfdDate">
                            <input type="hidden" id="mfdYearVal">
                            <input type="hidden" id="mfdMonthVal">
                            <input type="hidden" id="mfdDayVal">
                        </div>

                        <div>
                            <label class="label">Expiry Date <span class="label-hint">(EXP)</span></label>
                            <div class="date-selectors">
                                <div class="date-dropdown">
                                    <div class="date-trigger" id="expYearDisp" data-type="year" data-prefix="exp">
                                        <span>YYYY</span><i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="date-popup"><div class="grid-layout grid-years" id="expYearGrid"></div></div>
                                </div>
                                <div class="date-dropdown">
                                    <div class="date-trigger" id="expMonthDisp" data-type="month" data-prefix="exp">
                                        <span>MM</span><i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="date-popup"><div class="grid-layout grid-months" id="expMonthGrid"></div></div>
                                </div>
                                <div class="date-dropdown">
                                    <div class="date-trigger" id="expDayDisp" data-type="day" data-prefix="exp">
                                        <span>DD</span><i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="date-popup"><div class="grid-layout grid-days" id="expDayGrid"></div></div>
                                </div>
                            </div>
                            <input type="hidden" id="expDate">
                            <input type="hidden" id="expYearVal">
                            <input type="hidden" id="expMonthVal">
                            <input type="hidden" id="expDayVal">
                            <div id="expSuggestions" class="pill-group"></div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <label class="label">Period After Opening (PAO)</label>
                    <input type="text" class="input" id="usablePeriod" placeholder="e.g., 12 Months">
                    <div class="pill-group" id="usePills">
                        <button class="pill use-btn" data-val="3 Months">3 Months</button>
                        <button class="pill use-btn" data-val="6 Months">6 Months</button>
                        <button class="pill use-btn" data-val="12 Months">12 Months</button>
                        <button class="pill use-btn" data-val="24 Months">24 Months</button>
                    </div>
                </div>
            </div>

            <div class="action-row">
                <div class="form-group">
                    <label class="label">Labels to Print</label>
                    <div class="qty-control">
                        <button class="qty-btn" id="qtyMinus"><i class="fa-solid fa-minus"></i></button>
                        <input type="number" class="qty-input" id="stickerQty" value="1" min="1">
                        <button class="qty-btn" id="qtyPlus"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
                
                <button class="btn btn-primary btn-print-large" id="mainPrintBtn">
                    <i class="fa-solid fa-print"></i> Generate & Print Labels
                </button>
            </div>

        </div>
    </main>

    <aside class="side-panel">
        <div class="side-header">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Smart Tools
        </div>

        <div class="side-body">
            
            <div class="preview-container">
                <div class="preview-label">Live Format Preview</div>
                <div class="sticker-canvas">
                    <div class="sc-name" id="prevTitle">PRODUCT NAME</div>
                    <div class="sc-meta" id="prevMfd">MFD: DD/MM/YYYY</div>
                    <div class="sc-meta" id="prevExp">EXP: DD/MM/YYYY</div>
                    <div class="sc-meta" id="prevUse">USE WITHIN: —</div>
                </div>
            </div>

            <div class="decoder-widget">
                <label class="label">Batch Code Decoder</label>
                <p class="label-hint" style="margin: 0 0 0.75rem 0;">Extract dates from Julian formats.</p>
                
                <div class="decoder-input-group">
                    <input type="text" id="batchCodeInput" class="input" placeholder="e.g. 103192">
                    <button class="btn btn-primary" id="decodeBtn">Decode</button>
                </div>

                <div class="result-card" id="decodeResult">
                    <div class="result-title">Decoded Manufacture Date</div>
                    <div class="result-value" id="decodedMfdText">April 13, 2019</div>
                    <button class="btn-apply" id="applyMfdBtn">
                        <i class="fa-solid fa-check"></i> Apply to Form
                    </button>
                </div>

                <div class="error-msg" id="decodeError">
                    <i class="fa-solid fa-triangle-exclamation"></i> Unrecognized batch format.
                </div>

                <div class="info-panel">
                    <strong>Format Reference:</strong><br>
                    • <strong>DDDYY:</strong> 103<strong>19</strong> → 103rd day of 2019<br>
                    • <strong>DDDY:</strong> 103<strong>9</strong> → 103rd day of 2019<br>
                    • <strong>YYDDD:</strong> <strong>19</strong>103 → 2019, 103rd day
                </div>
            </div>

        </div>
    </aside>
</div>

<div id="print-container"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    const currYear = new Date().getFullYear();
    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

    let yearHtml = '';
    for (let y = currYear - 10; y <= currYear + 15; y++) yearHtml += `<div class="grid-item" data-val="${y}">${y}</div>`;
    $('#mfdYearGrid, #expYearGrid').html(yearHtml);

    let monthHtml = '';
    for (let m = 1; m <= 12; m++) { 
        let v = String(m).padStart(2,'0'); 
        monthHtml += `<div class="grid-item" data-val="${v}">${months[m-1]}</div>`; 
    }
    $('#mfdMonthGrid, #expMonthGrid').html(monthHtml);

    let dayHtml = '';
    for (let d = 1; d <= 31; d++) { 
        let v = String(d).padStart(2,'0'); 
        dayHtml += `<div class="grid-item" data-val="${v}">${v}</div>`; 
    }
    $('#mfdDayGrid, #expDayGrid').html(dayHtml);

    $('.date-trigger').on('click', function(e) {
        let popup = $(this).siblings('.date-popup');
        let isOpen = popup.hasClass('active');
        $('.date-popup').removeClass('active');
        $('.date-trigger').removeClass('open');
        if (!isOpen) { popup.addClass('active'); $(this).addClass('open'); }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.date-trigger').length && !$(e.target).closest('.date-popup').length) {
            $('.date-popup').removeClass('active');
            $('.date-trigger').removeClass('open');
        }
    });

    $(document).on('click', '.grid-item', function() {
        let btn = $(this);
        let popup = btn.closest('.date-popup');
        let display = popup.siblings('.date-trigger');
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
            $(`#${prefix}YearGrid .grid-item, #${prefix}MonthGrid .grid-item, #${prefix}DayGrid .grid-item`).removeClass('selected');
            return;
        }
        let [y, m, d] = dateStr.split('-');
        $(`#${prefix}YearVal`).val(y); $(`#${prefix}MonthVal`).val(m); $(`#${prefix}DayVal`).val(d);
        $(`#${prefix}YearDisp span`).text(y);
        $(`#${prefix}MonthDisp span`).text(months[parseInt(m)-1]);
        $(`#${prefix}DayDisp span`).text(d);
        $(`#${prefix}YearGrid .grid-item`).removeClass('selected').filter(`[data-val="${y}"]`).addClass('selected');
        $(`#${prefix}MonthGrid .grid-item`).removeClass('selected').filter(`[data-val="${m}"]`).addClass('selected');
        $(`#${prefix}DayGrid .grid-item`).removeClass('selected').filter(`[data-val="${d}"]`).addClass('selected');
    }

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

    $('#mfdDate').on('change', function() {
        let mfd = $(this).val();
        $('#expSuggestions').empty();
        if (!mfd) return;
        let date = new Date(mfd);
        for (let i = 1; i <= 3; i++) {
            let ny = new Date(date);
            ny.setFullYear(date.getFullYear() + i);
            let ds = ny.getFullYear() + '-' + String(ny.getMonth()+1).padStart(2,'0') + '-' + String(ny.getDate()).padStart(2,'0');
            $('#expSuggestions').append(`<button class="pill exp-btn" data-date="${ds}">+${i} Year${i>1?'s':''}</button>`);
        }
    });

    $(document).on('click', '.exp-btn', function() {
        let d = $(this).data('date');
        syncToDate('exp', d);
        $('#expDate').val(d).trigger('change');
    });

    $(document).on('click', '.use-btn', function() {
        $('#usablePeriod').val($(this).data('val')).trigger('input');
    });

    let acTimer;
    $('#productName').on('keyup', function() {
        clearTimeout(acTimer);
        let val = $(this).val();
        if (val.length < 2) { $('#autocompleteList').hide(); return; }
        
        acTimer = setTimeout(() => {
            $.getJSON('index.php?action=search_product', { term: val }, function(data) {
                $('#autocompleteList').empty();
                if (data.length) {
                    data.forEach(item => { $('#autocompleteList').append(`<div class="ac-option">${item}</div>`); });
                    $('#autocompleteList').show();
                } else { $('#autocompleteList').hide(); }
            });
        }, 280);
    });

    $(document).on('click', '.ac-option', function() {
        $('#productName').val($(this).text()).trigger('input');
        $('#autocompleteList').hide();
    });

    $(document).on('click', e => { if (!$(e.target).closest('.ac-wrapper').length) $('#autocompleteList').hide(); });

    $('#qtyMinus').on('click', function() {
        let v = parseInt($('#stickerQty').val()) || 1;
        if (v > 1) $('#stickerQty').val(v - 1);
    });
    $('#qtyPlus').on('click', function() {
        let v = parseInt($('#stickerQty').val()) || 1;
        $('#stickerQty').val(v + 1);
    });

    function doPrint() {
        let mfd = $('#mfdDate').val();
        let exp = $('#expDate').val();
        let name = $('#productName').val().trim();
        let qty = parseInt($('#stickerQty').val()) || 1;
        let use = $('#usablePeriod').val();

        if (!name) { alert('Please enter a product name.'); return; }
        if (!mfd)  { alert('Please select a Manufacture Date.'); return; }
        if (!exp)  { alert('Please select an Expiry Date.'); return; }
        if (!use)  { alert('Please enter a usable period (PAO).'); return; }
        if (exp < mfd) { alert('Expiry Date cannot be earlier than Manufacture Date.'); return; }

        $.post('index.php', { action: 'save_product', product_name: name });

        let container = $('#print-container').empty();
        let labelsHtml = '';
        for (let i = 0; i < qty; i++) {
            labelsHtml += `
                <div class="print-label">
                    <div class="print-product">${name}</div>
                    <div class="print-date">MFD: ${fmtDate(mfd)}</div>
                    <div class="print-date">EXP: ${fmtDate(exp)}</div>
                    <div class="print-date">USE WITHIN: ${use}</div>
                </div>`;
        }
        
        // Wrap every 2 labels in a row to force correct page breaks
        let labels = $(labelsHtml);
        for(let i = 0; i < labels.length; i+=2) {
            let row = $('<div class="print-row"></div>');
            row.append(labels.slice(i, i+2));
            container.append(row);
        }
        
        setTimeout(() => window.print(), 80);
    }
    $('#mainPrintBtn, #topPrintBtn').on('click', doPrint);

    $('#decodeBtn').on('click', function() {
        let code = $('#batchCodeInput').val().toUpperCase();
        let digits = code.replace(/\D/g, '');
        let currY = new Date().getFullYear();
        let candidates = [];

        $('#decodeError').removeClass('active');
        $('#decodeResult').removeClass('active');

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
                
                // Heavy penalty for future dates
                if (c.date.getFullYear() > currY + 1) score += 20;
                
                // Bonus for explicit 2-digit years (more reliable than guessing the decade)
                if (c.fmt === 'DDDYY' || c.fmt === 'YYDDD') score -= 5;
                
                // Bonus for exact length match (consumes all digits provided without ignoring trailing characters)
                let expectedLen = (c.fmt === 'DDDYY' || c.fmt === 'YYDDD') ? 5 : 4;
                if (digits.length === expectedLen) score -= 10;
                
                if (score < minScore) { minScore = score; best = c.date; }
            });
            mfdDate = best;
        }

        if (mfdDate && !isNaN(mfdDate.getTime())) {
            let iso = mfdDate.getFullYear() + '-' + String(mfdDate.getMonth()+1).padStart(2,'0') + '-' + String(mfdDate.getDate()).padStart(2,'0');
            let display = mfdDate.toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' });
            
            $('#decodedMfdText').text(display);
            $('#applyMfdBtn').data('date', iso);
            $('#decodeResult').addClass('active');
        } else {
            $('#decodeError').addClass('active');
        }
    });

    $('#batchCodeInput').on('keydown', function(e) { if (e.key === 'Enter') $('#decodeBtn').click(); });

    $('#applyMfdBtn').on('click', function() {
        let iso = $(this).data('date');
        syncToDate('mfd', iso);
        $('#mfdDate').val(iso).trigger('change');
        $('#batchCodeInput').val('');
        $('#decodeResult').removeClass('active');
    });

});
</script>
</body>
</html>