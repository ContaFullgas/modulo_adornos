<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
require_admin();

require_once __DIR__ . '/lib/fpdf/fpdf.php';

$type = $_GET['type'] ?? 'reservations';
$pdf = new FPDF();
$pdf->AddPage();
// $pdf->SetFont('Arial','B',16);
// $pdf->Cell(0,10, 'Reporte - ' . ucfirst($type), 0,1,'C');
// $pdf->Ln(4);
// $pdf->SetFont('Arial','',10);

// Helper: convertir UTF-8 a lo que entiende FPDF (ISO-8859-1), transliterando y reemplazando
function toPdf($s){
    if($s === null) return '';
    // asegurar que es string
    $s = (string)$s;

    // reemplazos directos para caracteres problemáticos
    $repls = [
        "—" => " - ",
        "–" => " - ",
        "“" => '"',
        "”" => '"',
        "‘" => "'",
        "’" => "'",
        "…" => "...",
        "•" => "-",
        "−" => "-", // signo menos
        "\xC2\xA0" => " " // non-breaking space -> space
    ];
    $s = strtr($s, $repls);

    // quitar caracteres de control no deseados
    $s = preg_replace('/[\x00-\x1F\x7F]/u', '', $s);

    // intentar transliteración con iconv si está disponible
    if(function_exists('iconv')){
        $out = @iconv('UTF-8','ISO-8859-1//TRANSLIT', $s);
        if($out !== false) return $out;
    }
    // fallback razonable
    return utf8_decode($s);
}

if($type === 'reservations'){
    // Título en español
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10, toPdf('Reporte - Reservas'), 0,1,'C');
    $pdf->Ln(4);
    $pdf->SetFont('Arial','',10);

    $q = $conn->query("
        SELECT r.*, i.code AS item_code, i.description AS item_description,
               d.name AS dept_name, u.username as user_name
        FROM reservations r
        LEFT JOIN items i ON r.item_id=i.id
        LEFT JOIN departments d ON r.dept_id=d.id
        LEFT JOIN users u ON r.user_id=u.id
        ORDER BY r.reserved_at DESC
    ");

    while($row = $q->fetch_assoc()){

        // Línea principal en español
        $line1 =
            "Código: " . $row['item_code'] .
            " | Cantidad: " . $row['quantity'] .
            " | Departamento: " . $row['dept_name'];

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,6, toPdf($line1), 0,1);

        // Detalles
        $pdf->SetFont('Arial','',9);
        $multi =
            "Fecha: " . $row['reserved_at'] .
            " | Estado: " . $row['status'] .
            " | Notas: " . ($row['notes'] ?? '');

        $pdf->MultiCell(0,5, toPdf($multi));
        $pdf->Ln(2);
    }
}

elseif($type === 'returns'){
    $q = $conn->query("
        SELECT r.*, i.code AS item_name, i.description AS item_description, d.name AS dept_name, u.username AS handled_by_name
        FROM returns r
        LEFT JOIN items i ON r.item_id=i.id
        LEFT JOIN departments d ON r.dept_id=d.id
        LEFT JOIN users u ON r.handled_by=u.id
        ORDER BY r.returned_at DESC
    ");
    while($row = $q->fetch_assoc()){
        $pdf->SetFont('Arial','B',11);
        $itemDisplay = $row['item_name'];
        if (!empty($row['item_description'])) {
            $itemDisplay .= ' - ' . trim(mb_substr($row['item_description'], 0, 80));
        }
        $line1 = $itemDisplay . " - " . $row['quantity'] . " - " . ($row['dept_name'] ?? '');
        $pdf->Cell(0,6, toPdf($line1), 0,1);
        $pdf->SetFont('Arial','',9);

        $multi = "Fecha: " . ($row['returned_at'] ?? '') . " | Registró: " . ($row['handled_by_name'] ?? '') . " | Notas: " . ($row['notes'] ?? '');
        $pdf->MultiCell(0,5, toPdf($multi));
        $pdf->Ln(2);
    }

} else {
    // list items (ordenar por code)
    $q = $conn->query("SELECT * FROM items ORDER BY code");
    while($row = $q->fetch_assoc()){
        $code = $row['code'] ?? $row['id'];
        $desc = isset($row['description']) ? ' - ' . trim(mb_substr($row['description'], 0, 80)) : '';
        $line = $code . $desc . " - Total: ".$row['total_quantity']." - Disp: ".$row['available_quantity'];
        $pdf->Cell(0,6, toPdf($line), 0,1);
    }
}

$pdf->Output('I','reporte_'.$type.'.pdf');
exit;
