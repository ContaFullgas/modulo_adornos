<?php
require_once __DIR__ . '/../config/auth.php';
require_login(); // usuarios autenticados

require_once __DIR__ . '/lib/fpdf/fpdf.php';

/**
 * Debug (opcional):
 * abre el reporte con: report_pdf.php?type=reservations&debug=1
 */
$DEBUG_IMAGES = (isset($_GET['debug']) && $_GET['debug'] == '1');

/**
 * Log helper (se va al error_log del servidor).
 * Si quieres, puedes redirigir a un archivo con ini_set('error_log', 'ruta.log');
 */
function log_img($msg){
    error_log("[REPORT_PDF] " . $msg);
}

// ----------------------------------------------------
$type = $_GET['type'] ?? 'reservations';

$user = current_user();
$isAdmin = ($user && ($user['role'] === 'admin'));

// ---------- determinar filtro de departamento ----------
$deptParam = isset($_GET['dept']) ? intval($_GET['dept']) : 0;
$deptFilterSQL = ''; // por defecto sin filtro

if($isAdmin){
    if($deptParam > 0){
        $deptFilterSQL = "WHERE r.dept_id = " . $deptParam;
    } else {
        $deptFilterSQL = "";
    }
} else {
    $userDept = !empty($user['department_id']) ? intval($user['department_id']) : 0;
    if($userDept > 0){
        $deptFilterSQL = "WHERE r.dept_id = " . $userDept;
    } else {
        $deptFilterSQL = ""; // opcional: mostrar todo si no tiene dept
    }
}

// ---------- FPDF y helpers ----------
$pdf = new FPDF('P','mm','A4');
$pdf->SetAutoPageBreak(true, 12);

/**
 * Guardaremos temporales para borrarlos al final
 */
$tmpImagesToDelete = [];

function toPdf($s){
    if($s === null) return '';
    $s = (string)$s;
    $repls = [
        "—" => " - ", "–" => " - ",
        "“" => '"', "”" => '"',
        "‘" => "'", "’" => "'",
        "…" => "...", "•" => "-",
        "−" => "-", "\xC2\xA0" => " "
    ];
    $s = strtr($s, $repls);
    $s = preg_replace('/[\x00-\x1F\x7F]/u', '', $s);
    if(function_exists('iconv')){
        $out = @iconv('UTF-8','ISO-8859-1//TRANSLIT', $s);
        if($out !== false) return $out;
    }
    return utf8_decode($s);
}

function printTableHeader($pdf){
    $pdf->SetFont('Arial','B',9);
    $w = ['dept'=>35,'photo'=>20,'code'=>22,'desc'=>38,'qty'=>12,'user'=>20,'stat'=>18,'date'=>25];
    $pdf->Cell($w['dept'],8, toPdf('Departamento'), 1,0,'L');
    $pdf->Cell($w['photo'],8, toPdf('Foto'), 1,0,'C');
    $pdf->Cell($w['code'],8, toPdf('Código'), 1,0,'C');
    $pdf->Cell($w['desc'],8, toPdf('Descripción'), 1,0,'L');
    $pdf->Cell($w['qty'],8, toPdf('Cant.'), 1,0,'C');
    $pdf->Cell($w['user'],8, toPdf('Usuario'), 1,0,'L');
    $pdf->Cell($w['stat'],8, toPdf('Estado'), 1,0,'C');
    $pdf->Cell($w['date'],8, toPdf('Fecha'), 1,1,'L');
    $pdf->SetFont('Arial','',9);
}

function hasSpaceFor($pdf, $height){
    $bottomLimit = ($pdf->h ?? 297) - ($pdf->bMargin ?? 12);
    return ($pdf->GetY() + $height) <= $bottomLimit;
}

function cellFitText($pdf, $w, $h, $txt, $align='L', $fontName='Arial', $style='', $baseSize=9, $minSize=6, $ln = 0){
    $size = $baseSize;
    $pdf->SetFont($fontName, $style, $size);
    $maxWidth = $w - 2;
    $strWidth = $pdf->GetStringWidth($txt);

    while($strWidth > $maxWidth && $size > $minSize){
        $size -= 0.5;
        $pdf->SetFont($fontName, $style, $size);
        $strWidth = $pdf->GetStringWidth($txt);
    }
    if($strWidth > $maxWidth){
        $txtShort = $txt;
        while($pdf->GetStringWidth($txtShort . '...') > $maxWidth && mb_strlen($txtShort) > 0){
            $txtShort = mb_substr($txtShort, 0, -1);
        }
        $txt = $txtShort . '...';
    }
    $pdf->Cell($w, $h, $txt, 1, $ln, $align);
    $pdf->SetFont('Arial', '', 9);
}

/**
 * Convierte PNG “problemáticos” a JPG baseline (fondo blanco) para que FPDF no falle.
 * Retorna la ruta a usar (original o temporal). Si crea temporal, lo agrega a $tmpImagesToDelete.
 */
function fpdfSafeImagePath($imgPath, &$tmpImagesToDelete){
    if(!file_exists($imgPath)) return null;

    $info = @getimagesize($imgPath);
    if(!$info || empty($info['mime'])) return null;

    // Si es PNG, normalizar a JPG (evita problemas de alpha/bit-depth)
    if($info['mime'] === 'image/png'){
        if(!function_exists('imagecreatefrompng') || !function_exists('imagejpeg')){
            // No hay GD: no podemos convertir, devolvemos original (podría fallar en FPDF)
            return $imgPath;
        }

        $im = @imagecreatefrompng($imgPath);
        if(!$im) return null;

        $w = imagesx($im);
        $h = imagesy($im);

        $bg = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagecopy($bg, $im, 0, 0, 0, 0, $w, $h);

        $tmp = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'fpdf_' . md5($imgPath) . '.jpg';
        @imagejpeg($bg, $tmp, 85);

        imagedestroy($im);
        imagedestroy($bg);

        if(file_exists($tmp)){
            $tmpImagesToDelete[] = $tmp;
            return $tmp;
        }
        return null;
    }

    // JPG/GIF típicos
    return $imgPath;
}

// ---------- generar PDF ----------
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$title = ($type === 'reservations') ? 'Reporte - Reservas' : (($type === 'returns') ? 'Reporte - Devoluciones' : 'Reporte - Adornos');
$pdf->Cell(0,10, toPdf($title), 0,1,'C');
$pdf->Ln(2);

if($type === 'reservations'){
    $whereClause = $deptFilterSQL ? $deptFilterSQL : '';
    $qStr = "
        SELECT r.*, i.code AS item_code, i.description AS item_description, i.image AS item_image,
               d.name AS dept_name, u.username as user_name
        FROM reservations r
        LEFT JOIN items i ON r.item_id=i.id
        LEFT JOIN departments d ON r.dept_id=d.id
        LEFT JOIN users u ON r.user_id=u.id
        $whereClause
        ORDER BY r.reserved_at DESC
    ";
    $q = $conn->query($qStr);
    if(!$q){
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,6, toPdf('Error en la consulta: ' . $conn->error), 0,1);
        // limpiar temporales
        foreach($tmpImagesToDelete as $t){ @unlink($t); }
        $pdf->Output('I','reporte_reservas.pdf');
        exit;
    }

    printTableHeader($pdf);

    $rowH = 14; $imgW = 18; $imgH = 12;
    $w = ['dept'=>35,'photo'=>20,'code'=>22,'desc'=>38,'qty'=>12,'user'=>20,'stat'=>18,'date'=>25];

    while($row = $q->fetch_assoc()){
        if(!hasSpaceFor($pdf, $rowH + 2)){
            $pdf->AddPage();
            printTableHeader($pdf);
        }

        $dept = toPdf($row['dept_name'] ?? '');
        $code = toPdf($row['item_code'] ?? ($row['item_id'] ?? ''));
        $descRaw = $row['item_description'] ?? '';
        $descRaw = preg_replace("/\s+/", ' ', trim($descRaw));
        if(mb_strlen($descRaw) > 140) $descRaw = mb_substr($descRaw,0,140) . '...';
        $desc = toPdf($descRaw);

        $qty = (int)($row['quantity'] ?? 0);
        $userName = toPdf($row['user_name'] ?? '');
        $status = toPdf($row['status'] ?? '');
        $date = toPdf($row['reserved_at'] ?? '');

        // --- imagen ---
        $imageFile = trim((string)($row['item_image'] ?? ''));
        $imageFile = basename($imageFile); // evita rutas guardadas en BD (uploads/... o /uploads/...)

        $imgPath = __DIR__ . '/uploads/' . $imageFile;

        $pdf->Cell($w['dept'], $rowH, $dept, 1, 0, 'L');

        $xBefore = $pdf->GetX(); $yBefore = $pdf->GetY();
        $pdf->Cell($w['photo'], $rowH, '', 1, 0, 'C');

        if(!empty($imageFile)){
            if(file_exists($imgPath)){
                $imgX = $xBefore + ($w['photo'] - $imgW)/2;
                $imgY = $yBefore + ($rowH - $imgH)/2;

                if($DEBUG_IMAGES){
                    $info = @getimagesize($imgPath);
                    log_img("IMG existe: {$imgPath} | " . json_encode($info));
                }

                $safePath = fpdfSafeImagePath($imgPath, $tmpImagesToDelete);

                try {
                    if($safePath){
                        $pdf->Image($safePath, $imgX, $imgY, $imgW, $imgH);
                    } else if($DEBUG_IMAGES){
                        log_img("No se pudo preparar imagen: {$imgPath}");
                    }
                } catch (Throwable $e) {
                    log_img("FPDF Image ERROR: {$imgPath} | " . $e->getMessage());
                }

            } else {
                if($DEBUG_IMAGES){
                    log_img("IMG NO EXISTE: '{$imageFile}' => {$imgPath}");
                }
            }
        }

        $pdf->Cell($w['code'], $rowH, $code, 1, 0, 'C');
        cellFitText($pdf, $w['desc'], $rowH, $desc, 'L', 'Arial', '', 9, 6);
        $pdf->Cell($w['qty'], $rowH, (string)$qty, 1, 0, 'C');
        $pdf->Cell($w['user'], $rowH, $userName, 1, 0, 'L');
        $pdf->Cell($w['stat'], $rowH, $status, 1, 0, 'C');
        cellFitText($pdf, $w['date'], $rowH, $date, 'L', 'Arial', '', 9, 6, 1);
    }
    $q->close();

    $pdf->Ln(3);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(0,5, toPdf('Reporte generado: ' . date('Y-m-d H:i')), 0,1,'R');
}
elseif($type === 'returns'){
    $whereClause = $deptFilterSQL ? $deptFilterSQL : '';
    $qStr = "
        SELECT r.*, i.code AS item_code, i.description AS item_description, i.image AS item_image,
               d.name AS dept_name, u.username AS handled_by_name
        FROM returns r
        LEFT JOIN items i ON r.item_id=i.id
        LEFT JOIN departments d ON r.dept_id=d.id
        LEFT JOIN users u ON r.handled_by=u.id
        $whereClause
        ORDER BY r.returned_at DESC
    ";
    $q = $conn->query($qStr);
    if(!$q){
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,6, toPdf('Error en la consulta: ' . $conn->error), 0,1);
        foreach($tmpImagesToDelete as $t){ @unlink($t); }
        $pdf->Output('I','reporte_returns.pdf');
        exit;
    }

    printTableHeader($pdf);

    $rowH = 14; $imgW = 18; $imgH = 12;
    $w = ['dept'=>35,'photo'=>20,'code'=>22,'desc'=>38,'qty'=>12,'user'=>20,'stat'=>18,'date'=>25];

    while($row = $q->fetch_assoc()){
        if(!hasSpaceFor($pdf, $rowH + 2)){
            $pdf->AddPage();
            printTableHeader($pdf);
        }

        $dept = toPdf($row['dept_name'] ?? '');
        $code = toPdf($row['item_code'] ?? '');
        $descRaw = $row['item_description'] ?? '';
        $descRaw = preg_replace("/\s+/", ' ', trim($descRaw));
        if(mb_strlen($descRaw) > 140) $descRaw = mb_substr($descRaw,0,140) .'...';
        $desc = toPdf($descRaw);
        $qty = (int)($row['quantity'] ?? 0);
        $userName = toPdf($row['handled_by_name'] ?? '');
        $date = toPdf($row['returned_at'] ?? '');

        // --- imagen ---
        $imageFile = trim((string)($row['item_image'] ?? ''));
        $imageFile = basename($imageFile);

        $imgPath = __DIR__ . '/uploads/' . $imageFile;

        $pdf->Cell($w['dept'], $rowH, $dept, 1, 0, 'L');

        $xBefore = $pdf->GetX(); $yBefore = $pdf->GetY();
        $pdf->Cell($w['photo'], $rowH, '', 1, 0, 'C');

        if(!empty($imageFile)){
            if(file_exists($imgPath)){
                $imgX = $xBefore + ($w['photo'] - $imgW)/2;
                $imgY = $yBefore + ($rowH - $imgH)/2;

                if($DEBUG_IMAGES){
                    $info = @getimagesize($imgPath);
                    log_img("IMG existe: {$imgPath} | " . json_encode($info));
                }

                $safePath = fpdfSafeImagePath($imgPath, $tmpImagesToDelete);

                try {
                    if($safePath){
                        $pdf->Image($safePath, $imgX, $imgY, $imgW, $imgH);
                    } else if($DEBUG_IMAGES){
                        log_img("No se pudo preparar imagen: {$imgPath}");
                    }
                } catch (Throwable $e) {
                    log_img("FPDF Image ERROR: {$imgPath} | " . $e->getMessage());
                }

            } else {
                if($DEBUG_IMAGES){
                    log_img("IMG NO EXISTE: '{$imageFile}' => {$imgPath}");
                }
            }
        }

        $pdf->Cell($w['code'], $rowH, $code, 1, 0, 'C');
        cellFitText($pdf, $w['desc'], $rowH, $desc, 'L', 'Arial', '', 9, 6);
        $pdf->Cell($w['qty'], $rowH, (string)$qty, 1, 0, 'C');
        $pdf->Cell($w['user'], $rowH, $userName, 1, 0, 'L');
        $pdf->Cell($w['stat'], $rowH, toPdf('Devuelto'), 1, 0, 'C');
        cellFitText($pdf, $w['date'], $rowH, $date, 'L', 'Arial', '', 9, 6, 1);
    }
    $q->close();

    $pdf->Ln(3);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(0,5, toPdf('Reporte generado: ' . date('Y-m-d H:i')), 0,1,'R');
}
else {
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8, toPdf('Reporte - Adornos'), 0,1,'C');
    $pdf->Ln(3);
    $pdf->SetFont('Arial','',10);
    $q = $conn->query("SELECT * FROM items ORDER BY code");
    if($q){
        while($row = $q->fetch_assoc()){
            $code = $row['code'] ?? $row['id'];
            $desc = isset($row['description']) ? ' - ' . trim(mb_substr($row['description'], 0, 80)) : '';
            $line = $code . $desc . " - Total: ".$row['total_quantity']." - Disp: ".$row['available_quantity'];
            $pdf->Cell(0,6, toPdf($line), 0,1);
        }
        $q->close();
    } else {
        $pdf->Cell(0,6, toPdf('Error en la consulta: ' . $conn->error), 0,1);
    }
}

// ---- limpiar temporales antes de enviar el PDF ----
foreach($tmpImagesToDelete as $t){
    @unlink($t);
}

$pdf->Output('I','reporte_'.$type.'.pdf');
exit;
