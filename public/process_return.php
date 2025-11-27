<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener datos
    $res_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';
    
    // Validar datos básicos
    if ($res_id <= 0 || empty($new_status)) {
        header("Location: reservations.php?error=Datos inválidos");
        exit;
    }

    // Obtener información de la reserva actual
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $res_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$res) {
        header("Location: reservations.php?error=Reserva no encontrada");
        exit;
    }

    $current_role = current_user()['role'];
    $current_dept = (int)current_user()['department_id'];

    // --- LÓGICA DE PERMISOS Y CAMBIOS DE ESTADO ---

    // CASO 1: USUARIO SOLICITA DEVOLUCIÓN (reservado -> en_proceso)
    if ($new_status === 'en_proceso') {
        
        // Verificar permisos: Debe ser Admin O el usuario del departamento dueño de la reserva
        if ($current_role !== 'admin' && $current_dept !== (int)$res['dept_id']) {
            header("Location: reservations.php?error=No tienes permiso para modificar esta reserva");
            exit;
        }

        // Actualizar estado
        $update = $conn->prepare("UPDATE reservations SET status = 'en_proceso' WHERE id = ?");
        $update->bind_param("i", $res_id);
        
        if ($update->execute()) {
            header("Location: reservations.php?msg=Devolución solicitada correctamente");
        } else {
            header("Location: reservations.php?error=Error al actualizar");
        }
        $update->close();
        exit;
    }

    // CASO 2: ADMIN CONFIRMA RECEPCIÓN (en_proceso/reservado -> finalizado)
    // También mapeamos 'devuelto' como 'finalizado' para compatibilidad
    if ($new_status === 'finalizado' || $new_status === 'devuelto') {
        
        // Solo admin puede finalizar
        if ($current_role !== 'admin') {
            header("Location: reservations.php?error=Solo el administrador puede confirmar recepciones");
            exit;
        }

        // Verificar que no esté ya devuelto para no duplicar stock
        if ($res['status'] === 'finalizado' || $res['status'] === 'devuelto') {
            header("Location: reservations.php?error=Esta reserva ya fue finalizada anteriormente");
            exit;
        }

        // INICIAR TRANSACCIÓN (Para asegurar que todo se guarde o nada)
        $conn->begin_transaction();

        try {
            // 1. Actualizar estado de la reserva y fecha de retorno
            $updRes = $conn->prepare("UPDATE reservations SET status = 'finalizado', returned_at = NOW() WHERE id = ?");
            $updRes->bind_param("i", $res_id);
            $updRes->execute();
            $updRes->close();

            // 2. Devolver Stock al Inventario (Tabla items)
            $updItem = $conn->prepare("UPDATE items SET available_quantity = available_quantity + ? WHERE id = ?");
            $updItem->bind_param("ii", $res['quantity'], $res['item_id']);
            $updItem->execute();
            $updItem->close();

            // 3. Registrar en el historial (Tabla returns)
            $admin_id = current_user()['id'];
            $notes = "Devolución confirmada por Admin";
            
            $insHist = $conn->prepare("INSERT INTO returns (reservation_id, item_id, dept_id, quantity, notes, returned_at, handled_by) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
            $insHist->bind_param("iiiisi", $res_id, $res['item_id'], $res['dept_id'], $res['quantity'], $notes, $admin_id);
            $insHist->execute();
            $insHist->close();

            // Confirmar todo
            $conn->commit();
            header("Location: reservations.php?msg=Devolución confirmada y stock actualizado");

        } catch (Exception $e) {
            $conn->rollback(); // Si algo falla, deshacer cambios
            header("Location: reservations.php?error=Error al procesar: " . $e->getMessage());
        }
        exit;
    }

    // CASO 3: REVERTIR A NO RECIBIDO (Solo Admin)
    if ($new_status === 'reservado' && $current_role === 'admin') {
        // Solo actualizamos el estado visualmente
        $stmt = $conn->prepare("UPDATE reservations SET status = 'reservado' WHERE id = ?");
        $stmt->bind_param("i", $res_id);
        $stmt->execute();
        header("Location: reservations.php?msg=Estado revertido a No Recibido");
        exit;
    }

} else {
    // Si intentan entrar directo por URL
    header("Location: reservations.php");
    exit;
}