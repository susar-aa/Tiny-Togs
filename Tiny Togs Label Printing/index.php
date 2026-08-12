<?php
// Standalone DB Connection
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

// Handle AJAX Requests
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
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny Togs - Label Printing</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ios-bg: #f2f2f7;
            --ios-card: #ffffff;
            --ios-blue: #007aff;
            --ios-blue-hover: #0066d6;
            --ios-green: #34c759;
            --ios-gray-4: #d1d1d6;
            --ios-gray-5: #e5e5ea;
            --ios-gray-6: #f2f2f7;
            --ios-label: #1c1c1e;
            --ios-secondary-label: #6b6b70;
            --ios-radius: 18px;
            --ios-shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.04);
            --ios-font: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--ios-bg);
            font-family: var(--ios-font);
            color: var(--ios-label);
            -webkit-font-smoothing: antialiased;
        }

        .ios-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .ios-page-title {
            font-size: 1.85rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ios-label);
            margin: 0 0 0.4rem 0;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .ios-page-title .icon-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #007aff, #4aa3ff);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.35);
        }

        .ios-btn {
            border: none;
            border-radius: 980px;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.6rem 1.2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            line-height: 1;
        }

        .ios-btn-primary {
            background: var(--ios-blue);
            color: #fff;
            box-shadow: 0 4px 14px rgba(0, 122, 255, 0.3);
        }

        .ios-btn-primary:hover {
            background: var(--ios-blue-hover);
            color: #fff;
        }

        .card {
            border-radius: var(--ios-radius);
            border: none;
            box-shadow: var(--ios-shadow-sm);
        }

        /* UI Styling */
        .preview-box {
            width: 100%;
            max-width: 300px;
            aspect-ratio: 2 / 1;
            border: 2px dashed var(--ios-blue);
            background: #fff;
            border-radius: 8px;
            padding: 10px;
            margin-top: 15px;
            font-family: Arial, sans-serif;
            color: #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: var(--ios-shadow-sm);
        }
        
        .preview-product {
            font-weight: 700;
            font-size: 14px;
            line-height: 1.1;
            margin-bottom: 6px;
            text-transform: uppercase;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .preview-date {
            font-size: 11px;
            line-height: 1.2;
            font-weight: 600;
        }

        .suggestion-btn {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
            margin-right: 0.5rem;
            margin-top: 0.5rem;
            border-radius: 12px;
            background: var(--ios-gray-5);
            border: none;
            color: var(--ios-blue);
            font-weight: 600;
            transition: background 0.2s;
        }
        .suggestion-btn:hover {
            background: var(--ios-gray-4);
        }

        .autocomplete-list {
            position: absolute;
            z-index: 1000;
            width: 100%;
            background: #fff;
            border: 1px solid var(--ios-gray-4);
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
            display: none;
        }
        .autocomplete-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid var(--ios-gray-5);
        }
        .autocomplete-item:hover {
            background: var(--ios-gray-6);
        }

        /* Print Styling for Zebra ZD230 (50mm x 25mm, 2 per row) */
        @media print {
            body * {
                visibility: hidden;
            }
            #print-container, #print-container * {
                visibility: visible;
            }
            #print-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100mm;
                margin: 0;
                padding: 0;
                display: flex;
                flex-wrap: wrap;
                align-content: flex-start;
            }
            .print-label {
                width: 50mm;
                height: 25mm;
                box-sizing: border-box;
                padding: 2mm 3mm;
                overflow: hidden;
                font-family: Arial, sans-serif;
                color: #000;
                background: #fff;
                display: flex;
                flex-direction: column;
                justify-content: center;
                page-break-inside: avoid;
            }
            .print-product {
                font-weight: 700;
                font-size: 9pt;
                line-height: 1;
                margin-bottom: 2mm;
                text-transform: uppercase;
                max-height: 18pt;
                overflow: hidden;
            }
            .print-date {
                font-size: 7.5pt;
                line-height: 1.1;
                font-weight: 600;
            }
            @page {
                size: 100mm 25mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="ios-wrap">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="ios-page-title">
                <div class="icon-badge"><i class="fa-solid fa-tags"></i></div>
                Label Printing
            </h1>
            <p class="text-muted mb-0">Generate optimized stickers for Zebra ZD230</p>
        </div>
        <a href="../" class="ios-btn" style="background: var(--ios-gray-5); color: var(--ios-label);">
            <i class="fa-solid fa-house"></i> Portal Home
        </a>
    </div>

    <div class="row">
        <!-- Left Panel: Form -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-body p-4">
                    <form id="printForm">
                        
                        <div class="mb-3 position-relative">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" class="form-control" id="productName" required autocomplete="off" placeholder="e.g. Aveeno Daily Moisturizing Lotion">
                            <div id="autocompleteList" class="autocomplete-list"></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date of Manufacture</label>
                                <input type="date" class="form-control" id="mfdDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date of Expiry</label>
                                <input type="date" class="form-control" id="expDate" required>
                                <div id="expSuggestions">
                                    <!-- Suggestions will be injected here -->
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Usable Period After Opening</label>
                            <input type="text" class="form-control" id="usablePeriod" required placeholder="e.g. 12 Months">
                            <div id="useSuggestions" class="mt-1">
                                <button type="button" class="suggestion-btn use-btn" data-val="6 Months">6 Months</button>
                                <button type="button" class="suggestion-btn use-btn" data-val="12 Months">12 Months</button>
                                <button type="button" class="suggestion-btn use-btn" data-val="24 Months">24 Months</button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Number of Stickers</label>
                            <input type="number" class="form-control" id="stickerQty" min="1" required style="max-width: 200px;">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Live Label Preview (50mm × 25mm)</label>
                            <div class="preview-box">
                                <div class="preview-product" id="prevTitle">PRODUCT NAME</div>
                                <div class="preview-date" id="prevMfd">MFD: DD/MM/YYYY</div>
                                <div class="preview-date" id="prevExp">EXP: DD/MM/YYYY</div>
                                <div class="preview-date" id="prevUse">USE WITHIN: -</div>
                            </div>
                        </div>

                        <button type="submit" class="ios-btn ios-btn-primary w-100 py-3" style="font-size: 1.1rem; justify-content: center;">
                            <i class="fa-solid fa-print"></i> PRINT LABELS
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <!-- Right Panel: Native Batch Code Decoder -->
        <div class="col-lg-4">
            <div class="card" style="background-color: #f8f9fa; border-radius: var(--ios-radius); border: none; box-shadow: var(--ios-shadow-sm);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-center"><i class="fa-solid fa-wand-magic-sparkles text-primary"></i> Batch Code Decoder</h5>
                    <p class="text-muted small mb-4 text-center">Instantly decode J&J / Aveeno batch codes without leaving this page! (Supports standard DDDY, DDDYY, and YYDDD formats).</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Enter Batch / Lot Code</label>
                        <div class="input-group">
                            <input type="text" id="batchCodeInput" class="form-control" placeholder="e.g., 1234V or 23123">
                            <button class="ios-btn ios-btn-primary" type="button" id="decodeBtn" style="border-radius: 0 12px 12px 0;">Decode</button>
                        </div>
                    </div>

                    <div id="decodeResult" class="d-none mt-4 p-3 rounded" style="background: #fff; border: 1px solid var(--ios-gray-5);">
                        <div class="text-center mb-2">
                            <span class="badge bg-success mb-2">Code Decoded Successfully</span>
                            <div style="font-size: 0.85rem; color: var(--ios-secondary-label);">Manufacture Date:</div>
                            <div id="decodedMfdText" class="fw-bold fs-5 text-dark mt-1"></div>
                        </div>
                        <button type="button" id="applyMfdBtn" class="ios-btn w-100 mt-3" style="background: var(--ios-gray-5); color: var(--ios-blue); justify-content: center; font-size: 0.9rem;">
                            <i class="fa-solid fa-arrow-left"></i> Use this MFD
                        </button>
                    </div>

                    <div id="decodeError" class="d-none mt-3 text-danger small text-center fw-bold">
                        Could not recognize this batch code format.
                    </div>

                    <div class="mt-4 p-3 rounded" style="background: rgba(0, 122, 255, 0.1); border-left: 4px solid var(--ios-blue);">
                        <strong class="d-block mb-1" style="color: var(--ios-blue); font-size: 0.8rem;">How it works:</strong>
                        <p class="small text-muted mb-0" style="font-size: 0.75rem;">Aveeno uses Julian dates. A code like <strong>12324</strong> means the 123rd day of 2024. This tool calculates the exact calendar date automatically!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Container (Hidden on screen) -->
<div id="print-container"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ---- Live Preview Update ----
    function updatePreview() {
        $('#prevTitle').text($('#productName').val() || 'PRODUCT NAME');
        
        let mfd = $('#mfdDate').val();
        $('#prevMfd').text('MFD: ' + (mfd ? formatDate(mfd) : 'DD/MM/YYYY'));
        
        let exp = $('#expDate').val();
        $('#prevExp').text('EXP: ' + (exp ? formatDate(exp) : 'DD/MM/YYYY'));
        
        $('#prevUse').text('USE WITHIN: ' + ($('#usablePeriod').val() || '-'));
    }

    $('#printForm input').on('input change', updatePreview);

    function formatDate(dateString) {
        if (!dateString) return '';
        const parts = dateString.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateString;
    }

    // ---- Date Logic ----
    $('#mfdDate').on('change', function() {
        const mfd = $(this).val();
        if (mfd) {
            const date = new Date(mfd);
            $('#expSuggestions').empty();
            
            for(let i=1; i<=3; i++) {
                const nextYear = new Date(date);
                nextYear.setFullYear(date.getFullYear() + i);
                const ds = nextYear.toISOString().split('T')[0];
                $('#expSuggestions').append(`<button type="button" class="suggestion-btn exp-btn" data-date="${ds}">+${i} Year${i>1?'s':''}</button>`);
            }
        }
    });

    $(document).on('click', '.exp-btn', function() {
        $('#expDate').val($(this).data('date')).trigger('change');
    });

    $('.use-btn').on('click', function() {
        $('#usablePeriod').val($(this).data('val')).trigger('input');
    });

    // ---- Autocomplete ----
    let timeout = null;
    $('#productName').on('keyup', function() {
        clearTimeout(timeout);
        const val = $(this).val();
        if (val.length < 2) {
            $('#autocompleteList').hide();
            return;
        }
        timeout = setTimeout(() => {
            $.getJSON('index.php?action=search_product', { term: val }, function(data) {
                $('#autocompleteList').empty();
                if(data.length > 0) {
                    data.forEach(item => {
                        $('#autocompleteList').append(`<div class="autocomplete-item">${item}</div>`);
                    });
                    $('#autocompleteList').show();
                } else {
                    $('#autocompleteList').hide();
                }
            });
        }, 300);
    });

    $(document).on('click', '.autocomplete-item', function() {
        $('#productName').val($(this).text()).trigger('input');
        $('#autocompleteList').hide();
    });

    // Hide autocomplete when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.position-relative').length) {
            $('#autocompleteList').hide();
        }
    });

    // ---- Form Submit (Printing) ----
    $('#printForm').on('submit', function(e) {
        e.preventDefault();
        
        const mfd = $('#mfdDate').val();
        const exp = $('#expDate').val();
        
        if (exp < mfd) {
            alert('Expiry Date cannot be earlier than Manufacture Date.');
            return;
        }
        
        const name = $('#productName').val();
        const qty = parseInt($('#stickerQty').val());
        const use = $('#usablePeriod').val();
        
        // Save product name via AJAX
        $.post('index.php', { action: 'save_product', product_name: name });

        // Generate Print HTML
        const container = $('#print-container');
        container.empty();
        
        for (let i = 0; i < qty; i++) {
            const label = `
                <div class="print-label">
                    <div class="print-product">${name}</div>
                    <div class="print-date">MFD: ${formatDate(mfd)}</div>
                    <div class="print-date">EXP: ${formatDate(exp)}</div>
                    <div class="print-date">USE WITHIN: ${use}</div>
                </div>
            `;
            container.append(label);
        }

        // Trigger browser print
        setTimeout(() => {
            window.print();
        }, 100);
    });

    // ---- Native Batch Code Decoder Logic ----
    $('#decodeBtn').on('click', function() {
        let code = $('#batchCodeInput').val().toUpperCase();
        let digits = code.replace(/\D/g, ''); // Extract only numbers
        let mfdDate = null;
        let currentYear = new Date().getFullYear();
        let candidates = [];

        $('#decodeError').addClass('d-none');
        $('#decodeResult').addClass('d-none');

        if (digits.length >= 4) {
            let ddd1 = parseInt(digits.substring(0, 3));
            let yy1_2 = digits.length >= 5 ? parseInt(digits.substring(3, 5)) : null;
            let y1_1 = parseInt(digits.substring(3, 4));
            
            let yy2 = digits.length >= 5 ? parseInt(digits.substring(0, 2)) : null;
            let ddd2 = digits.length >= 5 ? parseInt(digits.substring(2, 5)) : null;

            // 1. Try DDDYY format (e.g. 103192 -> 103rd day, 2019)
            if (ddd1 >= 1 && ddd1 <= 366 && yy1_2 !== null) {
                let d = new Date(2000 + yy1_2, 0);
                d.setDate(ddd1);
                candidates.push({ date: d, format: 'DDDYY' });
            }
            
            // 2. Try YYDDD format (e.g. 23103 -> 2023, 103rd day)
            if (ddd2 >= 1 && ddd2 <= 366 && yy2 !== null) {
                let d = new Date(2000 + yy2, 0);
                d.setDate(ddd2);
                candidates.push({ date: d, format: 'YYDDD' });
            }

            // 3. Try DDDY format (e.g. 1039 -> 103rd day, 2019/2029)
            if (ddd1 >= 1 && ddd1 <= 366) {
                let potentialYear = Math.floor(currentYear / 10) * 10 + y1_1;
                // If it's more than 1 year in the future, it's probably from the previous decade
                if (potentialYear > currentYear + 1) potentialYear -= 10;
                let d = new Date(potentialYear, 0);
                d.setDate(ddd1);
                candidates.push({ date: d, format: 'DDDY' });
            }
        }

        // Pick the most plausible date
        if (candidates.length > 0) {
            let best = null;
            let minScore = 9999;
            
            candidates.forEach(c => {
                let yDiff = Math.abs(c.date.getFullYear() - currentYear);
                let score = yDiff;

                // Heavily penalize future dates (unless they are within 1 year)
                if (c.date.getFullYear() > currentYear + 1) score += 20;

                // Penalize DDDY format if the user provided 5 or more digits
                // (Because DDDYY or YYDDD utilizes more of the provided information)
                if (c.format === 'DDDY' && digits.length >= 5) {
                    score += 50;
                }
                
                // Slight tie-breaker: DDDYY is more common than YYDDD for Aveeno
                if (c.format === 'YYDDD') {
                    score += 1;
                }
                
                if (score < minScore) {
                    minScore = score;
                    best = c.date;
                }
            });
            mfdDate = best;
        }

        if (mfdDate && !isNaN(mfdDate.getTime())) {
            let isoDate = mfdDate.getFullYear() + '-' + String(mfdDate.getMonth() + 1).padStart(2, '0') + '-' + String(mfdDate.getDate()).padStart(2, '0');
            let displayDate = mfdDate.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
            
            $('#decodedMfdText').text(displayDate);
            $('#applyMfdBtn').data('date', isoDate);
            $('#decodeResult').removeClass('d-none');
        } else {
            $('#decodeError').removeClass('d-none');
        }
    });

    $('#applyMfdBtn').on('click', function() {
        let isoDate = $(this).data('date');
        $('#mfdDate').val(isoDate).trigger('change');
        $('#batchCodeInput').val('');
        $('#decodeResult').addClass('d-none');
    });
});
</script>
</body>
</html>
