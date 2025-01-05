<?php
include_once "conexion.php";

header('Content-Type: application/json'); 

class ModeloVehiculos {

    public static function obtenerVehiculos() {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            $sql = "SELECT 
                v.matricula_vehiculo,
                v.titulo_vehiculo,
                v.imagen_vehiculo,
                v.año_vehiculo,
                v.color_vehiculo,
                v.combustible_vehiculo,
                v.pasajeros_vehiculo,
                v.transmision_vehiculo,
                v.precio_vehiculo,
                t.tipo_vehiculo,
                d.estado_disponibilidad,
                det.marca_vehiculo,
                det.modelo_vehiculo,
                det.caracteristicas
            FROM 
                vehiculos v
            JOIN 
                tipo_vehiculos t ON v.id_tipo_vehiculo = t.id_tipo_vehiculo
            JOIN 
                disponibilidad_vehiculo d ON v.id_disponibilidad_pertenece = d.id_disponibilidad_vehiculo
            JOIN 
                detalle_vehiculo det ON v.matricula_vehiculo = det.matricula_vehiculo";
    
            $stmt = $con->prepare($sql);
            $stmt->execute();
    
            $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($vehiculos);
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al obtener vehículos: ' . $e->getMessage()]);
        }
    }

    public static function obtenerTiposVehiculos() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        // Modificamos la consulta para obtener el id_tipo_vehiculo y el nombre tipo_vehiculo
        $sql = "SELECT id_tipo_vehiculo, tipo_vehiculo FROM tipo_vehiculos";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Similar para obtener los años de vehículos
    public static function obtenerAniosVehiculos() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT DISTINCT año_vehiculo FROM vehiculos ORDER BY año_vehiculo DESC";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Similar para obtener transmisiones
    public static function obtenerTransmisiones() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT DISTINCT transmision_vehiculo FROM vehiculos";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Similar para obtener combustibles
    public static function obtenerCombustibles() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT DISTINCT combustible_vehiculo FROM vehiculos";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    public static function eliminarVehiculo($matriculaVehiculo) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Iniciar la transacción para asegurar que ambas eliminaciones se hagan correctamente
            $con->beginTransaction();
    
            // Eliminar el detalle del vehículo en la tabla 'detalle_vehiculo'
            $sqlDetalle = "DELETE FROM detalle_vehiculo WHERE matricula_vehiculo = ?";
            $stmtDetalle = $con->prepare($sqlDetalle);
            $stmtDetalle->bindParam(1, $matriculaVehiculo);  // Asegúrate de usar el índice 1
            $stmtDetalle->execute();
    
            // Eliminar el vehículo en la tabla 'vehiculos'
            $sqlVehiculo = "DELETE FROM vehiculos WHERE matricula_vehiculo = ?";
            $stmtVehiculo = $con->prepare($sqlVehiculo);
            $stmtVehiculo->bindParam(1, $matriculaVehiculo);  // Asegúrate de usar el índice 1
            $stmtVehiculo->execute();
    
            // Confirmar la transacción
            $con->commit();
    
            // Responder con éxito
            echo json_encode(['mensaje' => 'Vehículo eliminado exitosamente']);
        } catch (PDOException $e) {
            // En caso de error, hacer rollback de la transacción
            $con->rollBack();
            echo json_encode(['mensaje' => 'Error al eliminar vehículo: ' . $e->getMessage()]);
        }
    }
    public static function registrarVehiculo() {
        // Comprobar si la solicitud es de tipo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Crear conexión a la base de datos
                $objetoConexion = new Conexion();
                $con = $objetoConexion->conectar();
                
                // Obtener los datos enviados en la solicitud
                $matriculaVehiculo = $_POST['matriculaVehiculo'];
                $tituloVehiculo = $_POST['tituloVehiculo'];
                $marcaVehiculo = $_POST['marcaVehiculo'];
                $modeloVehiculo = $_POST['modeloVehiculo'];
                $anioVehiculo = $_POST['anioVehiculo'];
                $colorVehiculo = $_POST['colorVehiculo'];
                $combustibleVehiculo = $_POST['combustibleVehiculo'];
                $pasajerosVehiculo = $_POST['pasajerosVehiculo'];
                $transmisionVehiculo = $_POST['transmisionVehiculo'];
                $precioVehiculo = $_POST['precioVehiculo'];
                $tipoVehiculo = $_POST['tipoVehiculo'];
                $caracteristicas = $_POST['caracteristicas'];
    
                // Validación: Verificar si el id_tipo_vehiculo existe en la tabla tipo_vehiculos
                $sqlVerificarTipo = "SELECT COUNT(*) FROM tipo_vehiculos WHERE id_tipo_vehiculo = :tipo";
                $stmtVerificarTipo = $con->prepare($sqlVerificarTipo);
                $stmtVerificarTipo->bindParam(':tipo', $tipoVehiculo);
                $stmtVerificarTipo->execute();
                $existeTipo = $stmtVerificarTipo->fetchColumn();
    
                if ($existeTipo == 0) {
                    // Si el tipo de vehículo no existe, devolver un mensaje de error
                    echo json_encode(['mensaje' => 'El tipo de vehículo no existe']);
                    return; // Salir de la función para evitar el registro
                }
    
                // Comprobar si la imagen fue subida correctamente
                if (isset($_FILES['imagen_vehiculo']) && $_FILES['imagen_vehiculo']['error'] == 0) {
                    // Obtener la extensión de la imagen
                    $imageFileType = strtolower(pathinfo($_FILES['imagen_vehiculo']['name'], PATHINFO_EXTENSION));
    
                    // Verificar si la extensión es una de las permitidas
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
                    if (!in_array($imageFileType, $allowedExtensions)) {
                        echo json_encode(['mensaje' => 'Formato de imagen no permitido.']);
                        return;
                    }
    
                    // Generar un nombre único para la imagen
                    $imageName = uniqid('vehiculo_') . '.' . $imageFileType;
    
                    // Definir la carpeta de destino donde se guardarán las imágenes
                    $targetDirectory = "../fotosVehiculos/";
    
                    // Mover el archivo de imagen a la carpeta de destino
                    $targetFile = $targetDirectory . $imageName;
                    move_uploaded_file($_FILES['imagen_vehiculo']['tmp_name'], $targetFile);
    
                    // Solo se guarda el nombre de la imagen (y no su contenido)
                    $imagenVehiculo = $imageName;
                } else {
                    // Si no se sube una imagen, manejarlo según lo necesites
                    throw new Exception('Error al subir la imagen.');
                }
    
                // Iniciar la transacción para asegurar que ambas inserciones se hagan correctamente
                $con->beginTransaction();
    
                // Insertar el vehículo en la tabla 'vehiculos'
                $sqlVehiculo = "INSERT INTO vehiculos (
                    matricula_vehiculo,  
                    titulo_vehiculo, 
                    imagen_vehiculo, 
                    año_vehiculo, 
                    color_vehiculo, 
                    combustible_vehiculo, 
                    pasajeros_vehiculo, 
                    transmision_vehiculo, 
                    precio_vehiculo, 
                    id_tipo_vehiculo, 
                    id_disponibilidad_pertenece
                ) VALUES (
                    :matricula, 
                    :titulo, 
                    :imagen, 
                    :anio, 
                    :color, 
                    :combustible, 
                    :pasajeros, 
                    :transmision, 
                    :precio, 
                    :tipo,  
                    1  
                )";
                
                $stmtVehiculo = $con->prepare($sqlVehiculo);
                $stmtVehiculo->bindParam(':matricula', $matriculaVehiculo);  
                $stmtVehiculo->bindParam(':titulo', $tituloVehiculo);
                $stmtVehiculo->bindParam(':imagen', $imagenVehiculo); 
                $stmtVehiculo->bindParam(':anio', $anioVehiculo);
                $stmtVehiculo->bindParam(':color', $colorVehiculo);
                $stmtVehiculo->bindParam(':combustible', $combustibleVehiculo);
                $stmtVehiculo->bindParam(':pasajeros', $pasajerosVehiculo);
                $stmtVehiculo->bindParam(':transmision', $transmisionVehiculo);
                $stmtVehiculo->bindParam(':precio', $precioVehiculo);
                $stmtVehiculo->bindParam(':tipo', $tipoVehiculo);
    
                $stmtVehiculo->execute();
    
                // Insertar los detalles del vehículo en la tabla 'detalle_vehiculo'
                $sqlDetalle = "INSERT INTO detalle_vehiculo (matricula_vehiculo, marca_vehiculo, modelo_vehiculo, caracteristicas) 
                               VALUES (:matricula, :marca, :modelo, :caracteristicas)";
                
                $stmtDetalle = $con->prepare($sqlDetalle);
                $stmtDetalle->bindParam(':matricula', $matriculaVehiculo);  
                $stmtDetalle->bindParam(':marca', $marcaVehiculo);
                $stmtDetalle->bindParam(':modelo', $modeloVehiculo);
                $stmtDetalle->bindParam(':caracteristicas', $caracteristicas);
                $stmtDetalle->execute();
    
                // Confirmar la transacción
                $con->commit();
    
                // Responder con éxito
                echo json_encode(['mensaje' => 'Vehículo registrado exitosamente']);
            } catch (PDOException $e) {
                // En caso de error, hacer rollback de la transacción
                $con->rollBack();
                echo json_encode(['mensaje' => 'Error al registrar vehículo: ' . $e->getMessage()]);
            } catch (Exception $e) {
                // Manejar otros errores
                echo json_encode(['mensaje' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['mensaje' => 'Método HTTP no permitido.']);
        }
    }

    public static function obtenerVehiculoPorMatricula($matriculaVehiculo) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            $sql = "SELECT 
                v.matricula_vehiculo,
                v.titulo_vehiculo,
                v.imagen_vehiculo,
                v.año_vehiculo,
                v.color_vehiculo,
                v.combustible_vehiculo,
                v.pasajeros_vehiculo,
                v.transmision_vehiculo,
                v.precio_vehiculo,
                t.tipo_vehiculo,
                d.estado_disponibilidad,
                det.marca_vehiculo,
                det.modelo_vehiculo,
                det.caracteristicas
            FROM 
                vehiculos v
            JOIN 
                tipo_vehiculos t ON v.id_tipo_vehiculo = t.id_tipo_vehiculo
            JOIN 
                disponibilidad_vehiculo d ON v.id_disponibilidad_pertenece = d.id_disponibilidad_vehiculo
            JOIN 
                detalle_vehiculo det ON v.matricula_vehiculo = det.matricula_vehiculo
            WHERE 
                v.matricula_vehiculo = :matricula";
    
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':matricula', $matriculaVehiculo);
            $stmt->execute();
    
            // Comprobamos si se encontró el vehículo
            $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($vehiculo) {
                echo json_encode($vehiculo);
            } else {
                echo json_encode(['mensaje' => 'Vehículo no encontrado']);
            }
        } catch (PDOException $e) {
            // Se mejoró la información de error
            echo json_encode(['mensaje' => 'Error al obtener vehículo: ' . $e->getMessage()]);
        }
    }
    
    public static function editarVehiculo() {
          // Comprobar si la solicitud es de tipo POST
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $objetoConexion = new Conexion();
                $con = $objetoConexion->conectar();
                
                // Verificar si las claves necesarias existen en la solicitud
                $matriculaVehiculo = isset($_POST['matriculaVehiculo']) ? $_POST['matriculaVehiculo'] : null;
                $tituloVehiculo = isset($_POST['tituloVehiculo']) ? $_POST['tituloVehiculo'] : null;
                $marcaVehiculo = isset($_POST['marcaVehiculo']) ? $_POST['marcaVehiculo'] : null;
                $modeloVehiculo = isset($_POST['modeloVehiculo']) ? $_POST['modeloVehiculo'] : null;
                $anioVehiculo = isset($_POST['anioVehiculo']) ? $_POST['anioVehiculo'] : null;
                $colorVehiculo = isset($_POST['colorVehiculo']) ? $_POST['colorVehiculo'] : null;
                $combustibleVehiculo = isset($_POST['combustibleVehiculo']) ? $_POST['combustibleVehiculo'] : null;
                $pasajerosVehiculo = isset($_POST['pasajerosVehiculo']) ? $_POST['pasajerosVehiculo'] : null;
                $transmisionVehiculo = isset($_POST['transmisionVehiculo']) ? $_POST['transmisionVehiculo'] : null;
                $precioVehiculo = isset($_POST['precioVehiculo']) ? $_POST['precioVehiculo'] : null;
                $tipoVehiculo = isset($_POST['tipoVehiculo']) ? $_POST['tipoVehiculo'] : null;
                $caracteristicas = isset($_POST['caracteristicas']) ? $_POST['caracteristicas'] : null;

                // Verificar si la matrícula del vehículo está definida
                if (!$matriculaVehiculo) {
                    echo json_encode(['mensaje' => 'La matrícula del vehículo es obligatoria.']);
                    return;
                }
                
                // Aquí puedes continuar con la actualización, utilizando los valores que ahora están verificados
                // Puedes incluir una validación adicional si alguna de las variables necesarias no está presente
                
                // Si la imagen es parte de la actualización
                if (isset($_FILES['imagen_vehiculo']) && $_FILES['imagen_vehiculo']['error'] == 0) {
                    // Obtener la extensión de la imagen
                    $imageFileType = strtolower(pathinfo($_FILES['imagen_vehiculo']['name'], PATHINFO_EXTENSION));
                    
                    // Verificar si la extensión es una de las permitidas
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
                    if (!in_array($imageFileType, $allowedExtensions)) {
                        echo json_encode(['mensaje' => 'Formato de imagen no permitido.']);
                        return;
                    }

                    // Generar un nombre único para la imagen
                    $imageName = uniqid('vehiculo_') . '.' . $imageFileType;
                    $targetDirectory = "../fotosVehiculos/";
                    $targetFile = $targetDirectory . $imageName;
                    move_uploaded_file($_FILES['imagen_vehiculo']['tmp_name'], $targetFile);
                    $imagenVehiculo = $imageName;
                } else {
                    // Si no se sube una nueva imagen, podrías mantener la imagen anterior
                    // Esto podría depender de tu lógica de negocio
                    $imagenVehiculo = isset($_POST['imagenActual']) ? $_POST['imagenActual'] : null;
                }

                // Ahora, realiza la actualización en la base de datos
                // Asegúrate de tener una transacción o una consulta adecuada

                $sqlUpdateVehiculo = "UPDATE vehiculos SET 
                    titulo_vehiculo = :titulo,
                    imagen_vehiculo = :imagen,
                    año_vehiculo = :anio,
                    color_vehiculo = :color,
                    combustible_vehiculo = :combustible,
                    pasajeros_vehiculo = :pasajeros,
                    transmision_vehiculo = :transmision,
                    precio_vehiculo = :precio,
                    id_tipo_vehiculo = :tipo
                    WHERE matricula_vehiculo = :matricula";
                
                $stmt = $con->prepare($sqlUpdateVehiculo);
                $stmt->bindParam(':matricula', $matriculaVehiculo);
                $stmt->bindParam(':titulo', $tituloVehiculo);
                $stmt->bindParam(':imagen', $imagenVehiculo);
                $stmt->bindParam(':anio', $anioVehiculo);
                $stmt->bindParam(':color', $colorVehiculo);
                $stmt->bindParam(':combustible', $combustibleVehiculo);
                $stmt->bindParam(':pasajeros', $pasajerosVehiculo);
                $stmt->bindParam(':transmision', $transmisionVehiculo);
                $stmt->bindParam(':precio', $precioVehiculo);
                $stmt->bindParam(':tipo', $tipoVehiculo);
                
                $stmt->execute();
                 // Actualizar la tabla 'detalle_vehiculo'
            $sqlDetalle = "UPDATE detalle_vehiculo SET 
            marca_vehiculo = :marca,
            modelo_vehiculo = :modelo,
            caracteristicas = :caracteristicas
        WHERE matricula_vehiculo = :matricula";

        $stmtDetalle = $con->prepare($sqlDetalle);
        $stmtDetalle->bindParam(':marca', $marcaVehiculo);
        $stmtDetalle->bindParam(':modelo', $modeloVehiculo);
        $stmtDetalle->bindParam(':caracteristicas', $caracteristicas);
        $stmtDetalle->bindParam(':matricula', $matriculaVehiculo);
        $stmtDetalle->execute();
                // Si todo es exitoso, devolver un mensaje
                echo json_encode(['mensaje' => 'Vehículo actualizado exitosamente']);
            } catch (Exception $e) {
                echo json_encode(['mensaje' => 'Error al actualizar vehículo: ' . $e->getMessage()]);
            }
        }


    }
    
    



    
    
   
    
      
    
    

   
    
    
    
}
?>
