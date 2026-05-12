<?php
session_start();

// Incluir conexión a la base de datos
include("../admin/conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit;
}

// Obtener el estado seleccionado del filtro
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

try {
    // Obtener el ID del usuario actual
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $_SESSION['usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        throw new Exception("Usuario no encontrado");
    }

    // Construir la consulta base
    $sql = "
        SELECT 
            p.id_pedido,
            p.fecha_pedido as fecha,
            p.estado,
            p.total,
            GROUP_CONCAT(CONCAT(d.cantidad, 'x ', pr.nombre_producto) SEPARATOR ', ') as productos
        FROM pedidos p
        LEFT JOIN detalles d ON p.id_pedido = d.id_pedido
        LEFT JOIN productos pr ON d.id_producto = pr.id_producto
        WHERE p.id_usuario = ?
    ";

    // Agregar filtro de estado si no es 'todos'
    if ($estado_filtro !== 'todos') {
        $sql .= " AND p.estado = ?";
    }

    $sql .= " GROUP BY p.id_pedido ORDER BY p.fecha_pedido DESC";

    $stmt = $conexion->prepare($sql);
    
    if ($estado_filtro !== 'todos') {
        $stmt->bind_param("is", $usuario['id_usuario'], $estado_filtro);
    } else {
        $stmt->bind_param("i", $usuario['id_usuario']);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $pedidos = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error = "Error al obtener los pedidos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Madam Blanca</title>
    <link rel="stylesheet" href="../assets/css/principal.css">
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">
    <link href="./assets/icon/image-removebg-preview.png" rel="stylesheet">
	<link href="css/line.css" rel="stylesheet">


    <style>
        .pedidos-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .filtro-estado {
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .filtro-estado select {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }

        .filtro-estado select:focus {
            outline: none;
            border-color: #007bff;
        }

        .pedido-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .pedido-numero {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }

        .pedido-fecha {
            color: #666;
        }

        .pedido-estado {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-procesando { background: #cce5ff; color: #004085; }
        .estado-enviado { background: #d4edda; color: #155724; }
        .estado-entregado { background: #d1e7dd; color: #0f5132; }
        .estado-cancelado { background: #f8d7da; color: #721c24; }
        .estado-pagado { background: #d1e7dd; color: #0f5132; }

        .pedido-detalles {
            margin-top: 1rem;
        }

        .pedido-productos {
            margin: 1rem 0;
            color: #666;
        }

        .pedido-total {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        .no-pedidos {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include './header.php'; ?>

    <div class="pedidos-container">
        <h1>Mis Pedidos</h1>
        
        <div class="filtro-estado">
            <form method="GET" action="">
                <label for="estado">Filtrar por estado:</label>
                <select name="estado" id="estado" onchange="this.form.submit()">
                    <option value="todos" <?php echo $estado_filtro === 'todos' ? 'selected' : ''; ?>>Todos los pedidos</option>
                    <option value="pendiente" <?php echo $estado_filtro === 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                    <option value="pagado" <?php echo $estado_filtro === 'pagado' ? 'selected' : ''; ?>>Pagados</option>
                    <option value="cancelado" <?php echo $estado_filtro === 'cancelado' ? 'selected' : ''; ?>>Cancelados</option>
                </select>
            </form>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (empty($pedidos)): ?>
            <div class="no-pedidos">
                <h2>No tienes pedidos realizados</h2>
                <p>¡Comienza a comprar en nuestra tienda!</p>
                <a href="tienda.php" class="btn">Ir a la tienda</a>
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div>
                            <span class="pedido-numero">Pedido #<?php echo htmlspecialchars($pedido['id_pedido']); ?></span>
                            <span class="pedido-fecha"> - <?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></span>
                        </div>
                        <span class="pedido-estado estado-<?php echo strtolower($pedido['estado']); ?>">
                            <?php echo htmlspecialchars($pedido['estado']); ?>
                        </span>
                    </div>
                    
                    <div class="pedido-detalles">
                        <div class="pedido-productos">
                            <strong>Productos:</strong><br>
                            <?php echo htmlspecialchars($pedido['productos']); ?>
                        </div>
                        <div class="pedido-total">
                            Total: $<?php echo number_format($pedido['total'], 2); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html> 