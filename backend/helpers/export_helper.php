<?php
/**
 * Export Helper - Apotek Ananda Jadimulya
 * 
 * Helper untuk export data ke format Excel (CSV) dan PDF (HTML-based).
 * Menggunakan native PHP tanpa library eksternal untuk kemudahan deployment.
 */

/**
 * Export data ke CSV (Excel-compatible)
 * 
 * @param string $filename Nama file output
 * @param array $headers Header kolom
 * @param array $data Data baris
 */
function exportToCSV(string $filename, array $headers, array $data): void {
    // Bersihkan output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM untuk UTF-8 di Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Header row
    fputcsv($output, $headers, ';');
    
    // Data rows
    foreach ($data as $row) {
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    exit;
}

/**
 * Export data ke PDF (HTML-to-PDF via browser print)
 * Generate halaman HTML yang diformat untuk print sebagai PDF
 * 
 * @param string $title Judul laporan
 * @param array $headers Header kolom
 * @param array $data Data baris
 * @param array $summary Ringkasan (opsional)
 */
function exportToPDFView(string $title, array $headers, array $data, array $summary = []): void {
    if (ob_get_level()) {
        ob_end_clean();
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($title) ?></title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none !important; }
            }
            
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                font-size: 11px;
                color: #333;
                margin: 20px;
            }
            
            .header-report {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #2c3e50;
                padding-bottom: 10px;
            }
            
            .header-report h1 {
                font-size: 18px;
                margin: 0 0 5px 0;
                color: #2c3e50;
            }
            
            .header-report h2 {
                font-size: 14px;
                margin: 0 0 5px 0;
                color: #34495e;
                font-weight: normal;
            }
            
            .header-report .date {
                font-size: 11px;
                color: #7f8c8d;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            
            th, td {
                border: 1px solid #bdc3c7;
                padding: 6px 8px;
                text-align: left;
            }
            
            th {
                background-color: #2c3e50;
                color: white;
                font-weight: 600;
            }
            
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            
            .summary {
                margin-top: 15px;
                padding: 10px;
                background: #ecf0f1;
                border-radius: 4px;
            }
            
            .summary p {
                margin: 3px 0;
                font-weight: 600;
            }
            
            .text-right {
                text-align: right;
            }
            
            .btn-print {
                padding: 10px 25px;
                background: #2c3e50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                margin: 15px 0;
            }
            
            .btn-print:hover {
                background: #34495e;
            }
        </style>
    </head>
    <body>
        <div class="no-print" style="text-align: center;">
            <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
            <button class="btn-print" onclick="window.close()" style="background:#e74c3c">✕ Tutup</button>
        </div>
        
        <div class="header-report">
            <h1>APOTEK ANANDA JADIMULYA</h1>
            <h2><?= htmlspecialchars($title) ?></h2>
            <div class="date">Dicetak: <?= date('d/m/Y H:i:s') ?></div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <?php foreach ($headers as $h): ?>
                        <th><?= htmlspecialchars($h) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= count($headers) ?>" style="text-align:center;color:#999;">
                            Tidak ada data
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <td><?= htmlspecialchars((string)$cell) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($summary)): ?>
            <div class="summary">
                <?php foreach ($summary as $key => $value): ?>
                    <p><?= htmlspecialchars($key) ?>: <?= htmlspecialchars((string)$value) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <script>
            // Auto print saat halaman dimuat (opsional)
            // window.onload = function() { window.print(); }
        </script>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Export data ke file Excel (XLSX) sederhana menggunakan XML Spreadsheet
 * 
 * @param string $filename Nama file
 * @param string $sheetTitle Judul sheet
 * @param array $headers Header kolom
 * @param array $data Data baris
 */
function exportToExcel(string $filename, string $sheetTitle, array $headers, array $data): void {
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" 
           xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    
    // Title row
    echo '<tr><td colspan="' . count($headers) . '" style="font-size:16px;font-weight:bold;text-align:center;">';
    echo htmlspecialchars($sheetTitle);
    echo '</td></tr>';
    
    // Date row
    echo '<tr><td colspan="' . count($headers) . '" style="text-align:center;">';
    echo 'Dicetak: ' . date('d/m/Y H:i:s');
    echo '</td></tr>';
    
    // Empty row
    echo '<tr><td colspan="' . count($headers) . '"></td></tr>';
    
    // Header row
    echo '<tr>';
    foreach ($headers as $h) {
        echo '<th style="background:#2c3e50;color:white;font-weight:bold;padding:8px;">';
        echo htmlspecialchars($h);
        echo '</th>';
    }
    echo '</tr>';
    
    // Data rows
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td style="padding:5px;">' . htmlspecialchars((string)$cell) . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
    exit;
}
