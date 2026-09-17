<?php
require_once __DIR__ . '/config/bootstrap.php';

use Config\Database;
use Models\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$logModel = new Log();

try {
    $db = Database::getConnection();

    // Query categories with main category association and product count
    $sql = "SELECT 
                COALESCE(c.main_category, 'Unassigned') AS main_category,
                c.category_name AS sub_category,
                c.including_items AS keywords,
                (SELECT COUNT(*) FROM products p WHERE p.current_category = c.category_name) AS product_count
            FROM categories c
            ORDER BY c.main_category ASC, c.category_name ASC";

    $stmt = $db->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also include main categories that don't have sub-categories assigned yet
    $sqlMain = "SELECT m.main_category_name
                FROM main_categories m
                WHERE m.main_category_name NOT IN (
                    SELECT DISTINCT main_category FROM categories WHERE main_category IS NOT NULL AND main_category != ''
                )
                ORDER BY m.main_category_name ASC";
    $stmtMain = $db->query($sqlMain);
    $unassignedMains = $stmtMain->fetchAll(PDO::FETCH_ASSOC);

    foreach ($unassignedMains as $um) {
        $categories[] = [
            'main_category' => $um['main_category_name'],
            'sub_category'  => '',
            'keywords'      => '',
            'product_count' => 0
        ];
    }

    // Sort combined result by main_category then sub_category
    usort($categories, function($a, $b) {
        $cmp = strcasecmp($a['main_category'], $b['main_category']);
        if ($cmp === 0) {
            return strcasecmp($a['sub_category'], $b['sub_category']);
        }
        return $cmp;
    });

    // Create Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Categories Master Data');

    // Set Header Row
    $headers = ['Main Category', 'Sub Category', 'Keywords / Included Items', 'Assigned Products'];

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        $sheet->getStyle($col . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8F0FE');
        $col++;
    }

    // Freeze pane below header
    $sheet->freezePane('A2');

    // Populate Data
    $rowNum = 2;
    foreach ($categories as $cat) {
        $sheet->setCellValue('A' . $rowNum, $cat['main_category'] ?? '');
        $sheet->setCellValue('B' . $rowNum, $cat['sub_category'] ?? '');
        $sheet->setCellValue('C' . $rowNum, $cat['keywords'] ?? '');
        $sheet->setCellValue('D' . $rowNum, (int)($cat['product_count'] ?? 0));
        $rowNum++;
    }

    // Auto-size columns
    $highestCol = $sheet->getHighestColumn();
    $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
    for ($i = 1; $i <= $highestColIndex; $i++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }

    // Set HTTP Headers for Excel File Download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Tiny_Togs_Category_Export_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (\Exception $e) {
    $logModel->record('category_export_exception', "Error exporting categories: " . $e->getMessage());
    header('Content-Type: text/html');
    echo "<h1>Export Error</h1><p>An error occurred while exporting categories: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
