<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
require_admin();

require_once __DIR__ . '/lib/fpdf/fpdf.php';

$type = $_GET['type'] ?? 'reservations';
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10, 'Reporte - ' . ucfirst($type), 0,1,'C');
$pdf->Ln(4);
$pdf->SetFont('Arial','',10);

if($type === 'reservations'){
    $q = $conn->query("SELECT r.*, i.name as item_name, d.name as dept_name, u.username as user_name
                       FROM reservations r
                       LEFT JOIN items i ON r.item_id=i.id
                       LEFT JOIN departments d ON r.dept_id=d.id
                       LEFT JOIN users u ON r.user_id=u.id
                       ORDER BY r.reserved_at DESC");
    while($row = $q->fetch_assoc()){
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,6, $row['item_name'] . " — " . $row['quantity'] . " — " . $row['dept_name'], 0,1);
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(0,5, "Fecha: ".$row['reserved_at'] . " | Estado: ".$row['status']." | Notas: ".$row['notes']);
        $pdf->Ln(2);
    }
} elseif($type === 'returns'){
    $q = $conn->query("SELECT r.*, i.name as item_name, d.name as dept_name, u.username as handled_by_name
                       FROM returns r
                       LEFT JOIN items i ON r.item_id=i.id
                       LEFT JOIN departments d ON r.dept_id=d.id
                       LEFT JOIN users u ON r.handled_by=u.id
                       ORDER BY r.returned_at DESC");
    while($row = $q->fetch_assoc()){
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,6, $row['item_name'] . " — " . $row['quantity'] . " — " . $row['dept_name'], 0,1);
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(0,5, "Fecha: ".$row['returned_at'] . " | Registró: ".$row['handled_by']." | Notas: ".$row['notes']);
        $pdf->Ln(2);
    }
} else {
    $q = $conn->query("SELECT * FROM items ORDER BY name");
    while($row = $q->fetch_assoc()){
        $pdf->Cell(0,6, $row['name'] . " — Total: ".$row['total_quantity']." — Disp: ".$row['available_quantity'], 0,1);
    }
}

$pdf->Output('I','reporte_'.$type.'.pdf');
exit;
