<?php
require_once __DIR__ . '/config/bootstrap.php';
use Config\Database;

// Handle AJAX Requests
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    $db = Database::getConnection();

    if ($action === 'search_product') {
        $term = $_GET['term'] ?? '';
        $stmt = $db->prepare("SELECT product_name FROM label_products WHERE product_name LIKE :term ORDER BY product_name ASC LIMIT 10");
        $stmt->execute(['term' => '%' . $term . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    if ($action === 'save_product') {
        $name = trim($_POST['product_name'] ?? '');
        if ($name) {
            $stmt = $db->prepare("INSERT IGNORE INTO label_products (product_name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }
}

$current_page = 'label-printing.php';
include __DIR__ . '/views/layout/header.php';
?>

<style>
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
        box-shadow: var(--ios-shadow);
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

<div class="ios-wrap">
    <div class="ios-page-header">
        <div>
            <h1 class="ios-page-title">
                <div class="icon-badge"><i class="fa-solid fa-print"></i></div>
                Label Printing
            </h1>
            <p class="ios-page-subtitle">Generate optimized stickers for Zebra ZD230</p>
        </div>
    </div>

    <div class="row">
        <!-- Left Panel: Form -->
        <div class="col-lg-8 mb-4">
            <div class="card" style="border-radius: var(--ios-radius); border: none; box-shadow: var(--ios-shadow-sm);">
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

                        <button type="submit" class="ios-btn ios-btn-primary w-100 py-3" style="font-size: 1.1rem;">
                            <i class="fa-solid fa-print"></i> PRINT LABELS
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <!-- Right Panel: CheckFresh Reference -->
        <div class="col-lg-4">
            <div class="card" style="border-radius: var(--ios-radius); border: none; box-shadow: var(--ios-shadow-sm); background-color: #f8f9fa;">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-flask"></i> Aveeno CheckFresh</h5>
                    <p class="text-muted small mb-4">Use the official CheckFresh website to manually check your Aveeno batch code and verify the date of manufacture. Then, enter the dates manually in the form.</p>
                    
                    <a href="https://www.checkfresh.com/aveeno.html?lang=en" target="_blank" class="ios-btn" style="background: var(--ios-blue); color: #fff; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open CheckFresh
                    </a>
                    
                    <div class="mt-4 p-3 rounded" style="background: rgba(0, 122, 255, 0.1); border-left: 4px solid var(--ios-blue); text-align: left;">
                        <strong class="d-block mb-1" style="color: var(--ios-blue); font-size: 0.9rem;">Workflow:</strong>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Check batch code on CheckFresh.</li>
                            <li>Read Manufacture Date.</li>
                            <li>Type it into the left panel.</li>
                            <li>Print your labels.</li>
                        </ol>
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
            $.getJSON('label-printing.php?action=search_product', { term: val }, function(data) {
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
        $.post('label-printing.php', { action: 'save_product', product_name: name });

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
});
</script>

<?php
// Include footer if exists, otherwise just close HTML
if (file_exists(__DIR__ . '/views/layout/footer.php')) {
    include __DIR__ . '/views/layout/footer.php';
} else {
    echo '</body></html>';
}
?>
