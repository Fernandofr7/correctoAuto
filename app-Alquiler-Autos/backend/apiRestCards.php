<?php
include_once "ConsultasCards.php"; // Asegúrate de que este archivo contenga las funciones necesarias

header('Content-Type: application/json');

// Obtiene la acción a realizar
$action = $_GET['action'] ?? ''; 

// Valida el método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'registrarVehiculo':
            // Registrar un vehículo
            ModeloVehiculos::registrarVehiculo();
            break;

        case 'editarVehiculo':
            // Editar un vehículo
            ModeloVehiculos::editarVehiculo();
            break;

        case 'eliminarVehiculo':
            if (isset($_POST['matricula_vehiculo'])) {
                $matriculaVehiculo = $_POST['matricula_vehiculo'];
                ModeloVehiculos::eliminarVehiculo($matriculaVehiculo);
            } else {
                echo json_encode(['mensaje' => 'Falta la matrícula del vehículo']);
            }
            break;

        default:
            echo json_encode(['mensaje' => 'Acción no válida']);
    }
}  elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'obtenerVehiculos':
            ModeloVehiculos::obtenerVehiculos();
            break;

        case 'obtenerTiposVehiculos':
            ModeloVehiculos::obtenerTiposVehiculos();
            break;

        case 'obtenerAniosVehiculos':
            ModeloVehiculos::obtenerAniosVehiculos();
            break;

        case 'obtenerTransmisiones':
            ModeloVehiculos::obtenerTransmisiones();
            break;

        case 'obtenerCombustibles':
            ModeloVehiculos::obtenerCombustibles();
            break;

        case 'obtenerVehiculoPorMatricula':
            // Verificamos que se haya pasado el parámetro 'matricula_vehiculo'
            if (isset($_GET['matricula_vehiculo'])) {
                $matriculaVehiculo = $_GET['matricula_vehiculo'];
                ModeloVehiculos::obtenerVehiculoPorMatricula($matriculaVehiculo);
            } else {
                echo json_encode(['mensaje' => 'Falta la matrícula del vehículo']);
            }
            break;

        default:
            echo json_encode(['mensaje' => 'Acción no válida']);
            break;
    }
} else {
    echo json_encode(['mensaje' => 'Método no permitido']);
}
?>
