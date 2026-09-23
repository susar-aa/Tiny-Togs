<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'tiny_togs';
$username = 'suzxlabs';
$password = 'Susara@200611003614';

if (!class_exists('PDO')) {
    die("<h1>Server Error</h1><p>The PHP 'PDO' extension is missing or disabled on this server. Please enable it in your php.ini file.</p>");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Ensure manufacturer_importers table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS manufacturer_importers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            importer_name VARCHAR(255) NOT NULL UNIQUE,
            address TEXT,
            contact_no VARCHAR(100),
            sls_cert VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

} catch (PDOException $e) {
    error_log("Database Connection Error in manufacturer.php: " . $e->getMessage());
    die("<h1>Database Connection Failed</h1><p>Please check your database parameters.</p>");
}

// Handle AJAX actions
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action === 'search_importer') {
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';
        $stmt = $pdo->prepare("SELECT importer_name, address, contact_no, sls_cert FROM manufacturer_importers WHERE importer_name LIKE :term ORDER BY importer_name ASC LIMIT 10");
        $stmt->execute(['term' => '%' . $term . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    if ($action === 'get_all_importers') {
        $stmt = $pdo->query("SELECT id, importer_name, address, contact_no, sls_cert FROM manufacturer_importers ORDER BY importer_name ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    if ($action === 'save_importer') {
        $name    = isset($_POST['importer_name']) ? trim($_POST['importer_name']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $contact = isset($_POST['contact_no']) ? trim($_POST['contact_no']) : '';
        $sls     = isset($_POST['sls_cert']) ? trim($_POST['sls_cert']) : '';

        if ($name) {
            try {
                $sql = "INSERT INTO manufacturer_importers (importer_name, address, contact_no, sls_cert)
                        VALUES (:name, :address, :contact, :sls)
                        ON DUPLICATE KEY UPDATE address = :address2, contact_no = :contact2, sls_cert = :sls2";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name'     => $name,
                    ':address'  => $address,
                    ':contact'  => $contact,
                    ':sls'      => $sls,
                    ':address2' => $address,
                    ':contact2' => $contact,
                    ':sls2'     => $sls
                ]);
                echo json_encode(['status' => 'success']);
            } catch (PDOException $e) {
                error_log("Error saving importer: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Importer name is required.']);
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
    <title>Manufacturer Label Studio | Tiny Togs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0F172A;
            --primary-hover: #1E293B;
            --accent: #F97316;
            --accent-hover: #EA580C;
            --accent-light: #FFF7ED;

            --success: #10B981;
            --success-light: #D1FAE5;
            --error: #EF4444;

            --bg-body: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-panel: #F1F5F9;

            --text-main: #0F172A;
            --text-muted: #64748B;
            --text-light: #94A3B8;

            --border-color: #E2E8F0;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;

            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-focus: 0 0 0 3px rgba(249, 115, 22, 0.15);

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
            width: 36px; height: 36px; background: var(--accent); color: white;
            border-radius: var(--radius-sm); display: flex; align-items: center;
            justify-content: center; font-size: 1.1rem; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
        }
        .brand-text { display: flex; flex-direction: column; line-height: 1.2; }
        .brand-title { font-weight: 700; font-size: 1.05rem; color: var(--text-main); letter-spacing: -0.01em; }
        .brand-subtitle { font-size: 0.75rem; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }

        .header-actions { display: flex; align-items: center; gap: 0.75rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.5rem 1rem; border-radius: var(--radius-sm); font-size: 0.875rem;
            font-weight: 600; cursor: pointer; border: 1px solid transparent;
            transition: all var(--transition-fast); text-decoration: none; font-family: inherit;
        }
        .btn-outline { background: transparent; border-color: var(--border-color); color: var(--text-main); }
        .btn-outline:hover { background: var(--bg-panel); border-color: #CBD5E1; }
        .btn-primary { background: var(--accent); color: white; box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: var(--shadow-md); }

        .workspace {
            flex: 1; display: grid; grid-template-columns: minmax(0, 1fr) 360px;
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
        .card-body { padding: 1.25rem; }

        .form-group { margin-bottom: 0.85rem; }
        .form-group:last-child { margin-bottom: 0; }
        .label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem; }
        .label-hint { font-weight: 400; color: var(--text-muted); font-size: 0.75rem; margin-left: 0.25rem; }
        .input, .textarea {
            width: 100%; padding: 0.6rem 0.875rem; border: 1px solid var(--border-color);
            border-radius: var(--radius-sm); font-family: inherit; font-size: 0.9rem;
            color: var(--text-main); background: var(--bg-surface); transition: all var(--transition-fast);
        }
        .textarea { resize: vertical; min-height: 60px; line-height: 1.4; }
        .input:focus, .textarea:focus { outline: none; border-color: var(--accent); box-shadow: var(--shadow-focus); }
        .input::placeholder, .textarea::placeholder { color: var(--text-light); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        .ac-wrapper { position: relative; }
        .ac-dropdown {
            position: absolute; z-index: 40; width: 100%; background: var(--bg-surface);
            border: 1px solid var(--border-color); border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg); max-height: 220px; overflow-y: auto;
            display: none; top: calc(100% + 4px);
        }
        .ac-option { padding: 0.75rem 1rem; cursor: pointer; font-size: 0.9rem; color: var(--text-main); transition: background var(--transition-fast); }
        .ac-option:hover { background: var(--accent-light); color: var(--accent-hover); }
        .ac-option:not(:last-child) { border-bottom: 1px solid var(--bg-panel); }

        .side-panel { background: var(--bg-surface); border-left: 1px solid var(--border-color); display: flex; flex-direction: column; }
        .side-header {
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex;
            align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.95rem; color: var(--text-main);
        }
        .side-header i { color: var(--accent); font-size: 1rem; }
        .side-body { padding: 1.25rem; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 1.25rem; }

        .preview-container {
            background: var(--bg-panel); border-radius: var(--radius-md); padding: 1rem;
            display: flex; flex-direction: column; align-items: center; border: 1px dashed var(--border-color);
        }
        .preview-label {
            font-size: 0.7rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; align-self: flex-start;
        }

        /* Live Sticker Preview Box (50mm x 25mm scaled aspect) */
        .sticker-canvas {
            width: 200px; height: 100px; background: #fff; border-radius: 4px; padding: 6px 10px;
            font-family: Arial, sans-serif; display: flex; flex-direction: column; justify-content: center;
            box-shadow: var(--shadow-sm); border: 1px solid #E2E8F0; overflow: hidden;
        }
        .sticker-canvas .sc-headline {
            font-size: 7px; font-weight: 800; line-height: 1; text-transform: uppercase;
            letter-spacing: 0.04em; margin-bottom: 3px; color: #000; border-bottom: 1px solid #ccc;
            display: inline-block; padding-bottom: 1px; align-self: flex-start;
        }
        .sticker-canvas .sc-importer {
            font-size: 9.5px; font-weight: 800; line-height: 1.15; text-transform: uppercase;
            margin-bottom: 3px; color: #000; word-break: break-word;
        }
        .sticker-canvas .sc-address {
            font-size: 7.5px; font-weight: 700; line-height: 1.15; color: #111;
            word-break: break-word; white-space: normal; margin-bottom: 2px;
        }
        .sticker-canvas .sc-info {
            font-size: 7.5px; font-weight: 700; line-height: 1.15; color: #111;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .presets-widget {
            background: var(--bg-body); border: 1px solid var(--border-color);
            border-radius: var(--radius-md); padding: 1rem;
        }
        .presets-title { font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .preset-pills { display: flex; flex-wrap: wrap; gap: 0.4rem; max-height: 140px; overflow-y: auto; }
        .preset-pill {
            padding: 0.3rem 0.7rem; border-radius: 99px; border: 1px solid var(--border-color);
            background: var(--bg-surface); color: var(--text-muted); font-size: 0.78rem;
            font-weight: 600; cursor: pointer; transition: all var(--transition-fast);
        }
        .preset-pill:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }

        .action-row { display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; align-items: end; margin-top: 0.5rem; }
        .qty-control {
            display: flex; align-items: center; border: 1px solid var(--border-color);
            border-radius: var(--radius-sm); background: var(--bg-surface); overflow: hidden;
            height: 42px;
        }
        .qty-btn {
            background: transparent; border: none; padding: 0 0.85rem; color: var(--text-muted);
            cursor: pointer; transition: background var(--transition-fast), color var(--transition-fast);
            height: 100%; font-size: 0.9rem;
        }
        .qty-btn:hover { background: var(--bg-panel); color: var(--text-main); }
        .qty-input {
            flex: 1; text-align: center; border: none; border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color); padding: 0; font-size: 1rem;
            font-weight: 700; color: var(--text-main); width: 50px; height: 100%; -moz-appearance: textfield;
        }
        .qty-input::-webkit-outer-spin-button, .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .btn-print-large { width: 100%; padding: 0.75rem; font-size: 0.95rem; display: flex; justify-content: center; height: 42px; align-items: center; }

        #print-container { display: none; }

        /* Exact Zebra ZD230 Print Specs (102mm x 25mm label width) */
        @media print {
            @page { size: 102mm 25mm; margin: 0; }
            html, body { width: 102mm; height: 25mm; margin: 0 !important; padding: 0 !important; }
            body * { visibility: hidden; }
            #print-container, #print-container * { visibility: visible; }
            #print-container {
                display: block;
                position: absolute; left: 0; top: 0; width: 102mm; margin: 0; padding: 0;
            }
            .print-row {
                width: 102mm;
                height: 25mm;
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                page-break-after: always;
                page-break-inside: avoid;
                break-after: page;
                break-inside: avoid;
                overflow: hidden;
            }
            .print-label {
                width: 50mm; height: 25mm; box-sizing: border-box; padding: 0.8mm 2.5mm; overflow: hidden;
                font-family: Arial, sans-serif; color: #000; background: #fff;
                display: flex; flex-direction: column; justify-content: center;
            }
            .print-label:first-child { margin-right: 2mm; }

            /* Standard Font Sizes */
            .print-headline { font-weight: 800; font-size: 6.5pt; line-height: 1; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.5mm; border-bottom: 0.5pt solid #000; display: inline-block; padding-bottom: 0.2mm; align-self: flex-start; }
            .print-importer { font-weight: 800; font-size: 8.8pt; line-height: 1.1; text-transform: uppercase; margin-bottom: 0.6mm; word-break: break-word; }
            .print-address  { font-size: 7pt; line-height: 1.12; font-weight: 700; word-break: break-word; white-space: normal; margin-bottom: 0.5mm; }
            .print-info     { font-size: 7pt; line-height: 1.12; font-weight: 700; white-space: nowrap; overflow: hidden; }

            /* Compact Layout for Longer Addresses */
            .print-label.compact .print-headline { font-size: 5.8pt; margin-bottom: 0.3mm; }
            .print-label.compact .print-importer { font-size: 8pt; margin-bottom: 0.4mm; }
            .print-label.compact .print-address  { font-size: 6.2pt; line-height: 1.08; margin-bottom: 0.4mm; }
            .print-label.compact .print-info     { font-size: 6.2pt; line-height: 1.08; }

            /* Ultra-Compact Layout for Very Long Addresses */
            .print-label.ultra-compact .print-headline { font-size: 5.2pt; margin-bottom: 0.2mm; }
            .print-label.ultra-compact .print-importer { font-size: 7.5pt; margin-bottom: 0.3mm; }
            .print-label.ultra-compact .print-address  { font-size: 5.5pt; line-height: 1.05; margin-bottom: 0.3mm; }
            .print-label.ultra-compact .print-info     { font-size: 5.5pt; line-height: 1.05; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-brand">
        <div class="brand-icon"><i class="fa-solid fa-truck-ramp-box"></i></div>
        <div class="brand-text">
            <span class="brand-title">Importer Label Studio</span>
            <span class="brand-subtitle">Tiny Togs</span>
        </div>
    </div>
    <div class="header-actions">
        <a href="../" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Portal
        </a>
        <a href="index.php" class="btn btn-outline" style="border-color: #3B82F6; color: #2563EB; background: #EFF6FF;">
            <i class="fa-solid fa-tags"></i> Standard Labels
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
                <h1 class="section-header">Configure Importer Sticker</h1>
                <p class="section-desc">Enter importer details, address, contact, and SLS certification to print stickers.</p>
            </div>

            <!-- Importer & Manufacturer Info Card -->
            <div class="card">
                <div class="card-body">

                    <div class="form-group">
                        <label class="label">Importer’s Name</label>
                        <div class="ac-wrapper">
                            <input type="text" class="input" id="importerName" autocomplete="off" placeholder="e.g. Tiny Togs Lanka (Pvt) Ltd">
                            <div id="autocompleteList" class="ac-dropdown"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Address</label>
                        <textarea class="textarea" id="importerAddress" placeholder="e.g. No 123, Galle Road, Colombo 03"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="label">Contact No</label>
                            <input type="text" class="input" id="contactNo" placeholder="e.g. +94 11 234 5678">
                        </div>

                        <div class="form-group">
                            <label class="label">SLS Certification</label>
                            <input type="text" class="input" id="slsCert" placeholder="e.g. SLS 1234:2020">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Quantity & Actions -->
            <div class="action-row">
                <div class="form-group">
                    <label class="label">Stickers to Print (QTY)</label>
                    <div class="qty-control">
                        <button class="qty-btn" id="qtyMinus"><i class="fa-solid fa-minus"></i></button>
                        <input type="number" class="qty-input" id="stickerQty" value="1" min="1">
                        <button class="qty-btn" id="qtyPlus"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <button class="btn btn-primary btn-print-large" id="mainPrintBtn">
                    <i class="fa-solid fa-print"></i> Generate &amp; Print Manufacturer Stickers
                </button>
            </div>

        </div>
    </main>

    <aside class="side-panel">
        <div class="side-header">
            <i class="fa-solid fa-eye"></i> Live Sticker Preview
        </div>

        <div class="side-body">

            <div class="preview-container">
                <div class="preview-label">50mm x 25mm Format Preview</div>
                <div class="sticker-canvas">
                    <div class="sc-headline">IMPORTER DETAILS</div>
                    <div class="sc-importer" id="prevImporter">FALCON STATIONERY PVT LTD</div>
                    <div class="sc-address" id="prevAddress">79, Dambakanda Estate, Kurunegala</div>
                    <div class="sc-info" id="prevContact">0761407875</div>
                    <div class="sc-info" id="prevSls">SLS: 123:2026</div>
                </div>
            </div>

            <div class="presets-widget">
                <div class="presets-title"><i class="fa-solid fa-bookmark me-1"></i> Saved Importers</div>
                <div class="preset-pills" id="presetContainer">
                    <span style="font-size: 0.78rem; color: var(--text-light);">Loading saved profiles...</span>
                </div>
            </div>

        </div>
    </aside>
</div>

<div id="print-container"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function formatSls(sls) {
    if (!sls) return '';
    let trimmed = sls.trim();
    if (/^sls/i.test(trimmed)) {
        return trimmed;
    }
    return 'SLS: ' + trimmed;
}

$(document).ready(function() {

    function updatePreview() {
        let name = $('#importerName').val().trim();
        let addr = $('#importerAddress').val().trim();
        let tel  = $('#contactNo').val().trim();
        let sls  = $('#slsCert').val().trim();

        $('#prevImporter').text(name || 'FALCON STATIONERY PVT LTD');
        $('#prevAddress').text(addr || '79, Dambakanda Estate, Kurunegala');
        $('#prevContact').text(tel || '0761407875');
        $('#prevSls').text(sls ? formatSls(sls) : 'SLS: 123:2026');
    }

    $('#importerName, #importerAddress, #contactNo, #slsCert').on('input', updatePreview);

    // Load saved importer presets
    function loadPresets() {
        $.getJSON('manufacturer.php?action=get_all_importers', function(data) {
            let container = $('#presetContainer').empty();
            if (data && data.length > 0) {
                data.forEach(item => {
                    let pill = $('<button class="preset-pill"></button>')
                        .text(item.importer_name)
                        .data('item', item);
                    container.append(pill);
                });
            } else {
                container.html('<span style="font-size: 0.78rem; color: var(--text-light);">No saved importers yet. Printed details auto-save here.</span>');
            }
        }).fail(function(err) {
            console.error("Error fetching importers: ", err);
        });
    }

    loadPresets();

    $(document).on('click', '.preset-pill', function() {
        let item = $(this).data('item');
        if (item) {
            $('#importerName').val(item.importer_name);
            $('#importerAddress').val(item.address || '');
            $('#contactNo').val(item.contact_no || '');
            $('#slsCert').val(item.sls_cert || '');
            updatePreview();
        }
    });

    // Autocomplete for Importer Name
    let acTimer;
    $('#importerName').on('keyup', function() {
        clearTimeout(acTimer);
        let val = $(this).val();
        if (val.length < 2) { $('#autocompleteList').hide(); return; }

        acTimer = setTimeout(() => {
            $.getJSON('manufacturer.php?action=search_importer', { term: val }, function(data) {
                $('#autocompleteList').empty();
                if (data && data.length) {
                    data.forEach(item => {
                        let opt = $('<div class="ac-option"></div>')
                            .text(item.importer_name)
                            .data('item', item);
                        $('#autocompleteList').append(opt);
                    });
                    $('#autocompleteList').show();
                } else {
                    $('#autocompleteList').hide();
                }
            });
        }, 250);
    });

    $(document).on('click', '.ac-option', function() {
        let item = $(this).data('item');
        if (item) {
            $('#importerName').val(item.importer_name);
            $('#importerAddress').val(item.address || '');
            $('#contactNo').val(item.contact_no || '');
            $('#slsCert').val(item.sls_cert || '');
            updatePreview();
        }
        $('#autocompleteList').hide();
    });

    $(document).on('click', e => {
        if (!$(e.target).closest('.ac-wrapper').length) $('#autocompleteList').hide();
    });

    // Quantity buttons
    $('#qtyMinus').on('click', function() {
        let v = parseInt($('#stickerQty').val()) || 1;
        if (v > 1) $('#stickerQty').val(v - 1);
    });
    $('#qtyPlus').on('click', function() {
        let v = parseInt($('#stickerQty').val()) || 1;
        $('#stickerQty').val(v + 1);
    });

    // Print logic matching Zebra ZD230 102mm x 25mm layout
    function doPrint() {
        let name = $('#importerName').val().trim();
        let addr = $('#importerAddress').val().trim();
        let tel  = $('#contactNo').val().trim();
        let sls  = $('#slsCert').val().trim();
        let qty  = parseInt($('#stickerQty').val()) || 1;

        if (!name) { alert("Please enter the Importer's Name."); return; }
        if (!addr) { alert("Please enter the Address."); return; }
        if (!tel)  { alert("Please enter the Contact No."); return; }
        if (!sls)  { alert("Please enter the SLS Certification info."); return; }

        let slsFormatted = formatSls(sls);
        let totalLen = name.length + addr.length + tel.length + slsFormatted.length;
        let sizeClass = '';
        if (totalLen > 110 || addr.length > 60) {
            sizeClass = 'ultra-compact';
        } else if (totalLen > 70 || addr.length > 35) {
            sizeClass = 'compact';
        }

        // Auto-save importer details to database
        $.post('manufacturer.php', {
            action: 'save_importer',
            importer_name: name,
            address: addr,
            contact_no: tel,
            sls_cert: sls
        }, function() {
            loadPresets();
        });

        let container = $('#print-container').empty();
        let currentRow = null;

        for (let i = 0; i < qty; i++) {
            if (i % 2 === 0) {
                currentRow = $('<div class="print-row"></div>');
                container.append(currentRow);
            }
            currentRow.append(`
                <div class="print-label ${sizeClass}">
                    <div class="print-headline">IMPORTER DETAILS</div>
                    <div class="print-importer">${name}</div>
                    <div class="print-address">${addr}</div>
                    ${tel ? `<div class="print-info">${tel}</div>` : ''}
                    ${slsFormatted ? `<div class="print-info">${slsFormatted}</div>` : ''}
                </div>
            `);
        }

        setTimeout(() => window.print(), 80);
    }

    $('#mainPrintBtn, #topPrintBtn').on('click', doPrint);

});
</script>
</body>
</html>
