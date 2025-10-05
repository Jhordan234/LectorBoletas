<?php
include 'config.php';

header('Content-Type: text/plain');

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'delete') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        try {
            $sql = "DELETE FROM gastos WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo "ok";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Error: ID inválido";
    }
} elseif ($action === 'edit') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre_cliente = isset($_POST['nombre_cliente']) ? $_POST['nombre_cliente'] : '';
    $importe_total = isset($_POST['importe_total']) ? (float)$_POST['importe_total'] : 0;

    if ($id > 0 && $nombre_cliente && $importe_total > 0) {
        try {
            $sql = "UPDATE gastos SET nombre_cliente = :nombre_cliente, importe_total = :importe_total WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_cliente', $nombre_cliente, PDO::PARAM_STR);
            $stmt->bindParam(':importe_total', $importe_total, PDO::PARAM_STR);
            $stmt->execute();
            echo "ok";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Error: Datos incompletos";
    }
} elseif ($action === 'view') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        try {
            $sql = "SELECT * FROM gastos WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($gasto) {
                echo json_encode($gasto);
            } else {
                echo json_encode(['error' => 'Gasto no encontrado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'ID inválido']);
    }
} elseif ($action === 'export') {
    try {
        $sql = "SELECT id, tipo_comprobante, serie_numero, nombre_cliente, fecha, moneda, subtotal, igv, importe_total
                FROM gastos ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $csv = "ID,Tipo,Serie,Cliente,Fecha,Moneda,Subtotal,IGV,Total\n";
        foreach ($gastos as $gasto) {
            $csv .= "\"{$gasto['id']}\",\"{$gasto['tipo_comprobante']}\",\"{$gasto['serie_numero']}\",\"{$gasto['nombre_cliente']}\",\"{$gasto['fecha']}\",\"{$gasto['moneda']}\",\"{$gasto['subtotal']}\",\"{$gasto['igv']}\",\"{$gasto['importe_total']}\"\n";
        }
        echo $csv;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} elseif ($action === 'chart_data') {
    $period = isset($_POST['period']) ? $_POST['period'] : 'mensual';
    $sql = "";
    if ($period === 'anual') {
        $sql = "SELECT TO_CHAR(fecha, 'YYYY') AS periodo, SUM(importe_total) AS total
                FROM gastos
                GROUP BY TO_CHAR(fecha, 'YYYY')
                ORDER BY TO_CHAR(fecha, 'YYYY')";
    } elseif ($period === 'semanal') {
        $sql = "SELECT TO_CHAR(fecha, 'IW') AS periodo, SUM(importe_total) AS total
                FROM gastos
                WHERE EXTRACT(YEAR FROM fecha) = EXTRACT(YEAR FROM CURRENT_DATE)
                GROUP BY TO_CHAR(fecha, 'IW'), EXTRACT(WEEK FROM fecha)
                ORDER BY EXTRACT(WEEK FROM fecha)";
    } else {
        $sql = "SELECT TO_CHAR(fecha, 'Mon') AS periodo, SUM(importe_total) AS total
                FROM gastos
                WHERE EXTRACT(YEAR FROM fecha) = EXTRACT(YEAR FROM CURRENT_DATE)
                GROUP BY TO_CHAR(fecha, 'Mon'), EXTRACT(MONTH FROM fecha)
                ORDER BY EXTRACT(MONTH FROM fecha)";
    }
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $data = [];
        foreach ($result as $row) {
            $labels[] = $row['periodo'];
            $data[] = (float)$row['total'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo "Error: Acción no válida";
}
?>