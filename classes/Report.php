<?php
// Report class for BMS

require_once 'vendor/autoload.php'; // Assuming Composer autoload for mPDF and PhpSpreadsheet

use \Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Report {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSalesReport($business_id, $from, $to) {
        $stmt = $this->conn->prepare("
            SELECT s.*, u.name as user_name, 
                   GROUP_CONCAT(CONCAT(p.name, ' (', si.qty, ' x ', si.unit_price, ')') SEPARATOR '; ') as items
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN sale_items si ON s.id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.id
            WHERE s.business_id = ? AND DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY s.id
            ORDER BY s.created_at DESC
        ");
        $stmt->bind_param("iss", $business_id, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getInventoryReport($business_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE business_id = ? ORDER BY stock_qty ASC");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProfitReport($business_id, $from, $to) {
        // Use Profit class
        require_once 'Profit.php';
        $profit = new Profit($this->conn);
        return $profit->calculate($business_id, $from, $to);
    }

    public function exportToPDF($report_type, $data) {
        $mpdf = new Mpdf();
        $html = "<h1>$report_type Report</h1><table border='1'><thead><tr>";
        if (!empty($data)) {
            $headers = array_keys($data[0]);
            foreach ($headers as $header) {
                $html .= "<th>$header</th>";
            }
            $html .= "</tr></thead><tbody>";
            foreach ($data as $row) {
                $html .= "<tr>";
                foreach ($row as $value) {
                    $html .= "<td>$value</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        }
        $mpdf->WriteHTML($html);
        $mpdf->Output("$report_type.pdf", 'D'); // Download
    }

    public function exportToExcel($report_type, $data) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if (!empty($data)) {
            $headers = array_keys($data[0]);
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }
            $row = 2;
            foreach ($data as $item) {
                $col = 'A';
                foreach ($item as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$report_type.xlsx\"");
        $writer->save('php://output');
        exit;
    }
}