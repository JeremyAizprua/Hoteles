<?php
class Hotel {
    private $conn;
    private $table_name = "hoteles";

    public $id;
    public $titulo;
    public $descripcion;
    public $ubicacion;
    public $provincia;
    public $costo;
    public $imagen_thumbnail;
    public $imagen_grande;
    public $publicar;
    public $activo;
    public $creado_por;
    public $categoria_id;

    // Constructor para inicializar la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Función para obtener los hoteles destacados
    public function obtenerHotelesDestacados() {
        $query = "SELECT h.*, c.nombre_categoria, u.nombre_usuario as creador 
                  FROM " . $this->table_name . " h 
                  LEFT JOIN categorias_hoteles c ON h.categoria_id = c.id 
                  LEFT JOIN usuarios u ON h.creado_por = u.id
                  WHERE h.destacado = 'S'";  // Filtrar solo hoteles destacados

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;  // Retorna el resultado
        } catch (Exception $e) {
            // Captura el error y lo muestra si ocurre una excepción
            echo "Error al obtener hoteles destacados: " . $e->getMessage();
        }
    }
    
    // Función para leer todos los hoteles activos
    public function leerTodos() {
        $query = "SELECT id, titulo FROM " . $this->table_name . " WHERE activo = 'activo'";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error al leer los hoteles: " . $e->getMessage();
        }
    }

    // Función para leer los detalles de un hotel por su ID
    public function leerDetallesHotel($id) {
        $query = "
            SELECT 
                h.*, 
                c.nombre_categoria,
                GROUP_CONCAT(h.provincia SEPARATOR ', ') AS provincias
            FROM 
                hoteles h
            LEFT JOIN 
                categorias_hoteles c ON h.categoria_id = c.id 
            WHERE 
                h.id = :id
            LIMIT 1;
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna los detalles del hotel
            } else {
                return false; // Si no se encuentra el hotel
            }
        } catch (Exception $e) {
            echo "Error al obtener los detalles del hotel: " . $e->getMessage();
        }
    }

    // Función para leer un hotel por su ID
    public function leerPorId($id) {
        $query = "SELECT * FROM hoteles WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna el hotel encontrado
            } else {
                return false; // Si no se encuentra el hotel
            }
        } catch (Exception $e) {
            echo "Error al obtener el hotel por ID: " . $e->getMessage();
        }
    }

    // Función para crear un nuevo hotel
    public function crear($titulo, $descripcion, $ubicacion, $provincia, $costo, $categoria_id, $creado_por, $publicar, $activo, $imagen_thumbnail, $imagen_grande) {
        $sql = "INSERT INTO hoteles (titulo, descripcion, ubicacion, provincia, costo, categoria_id, creado_por, publicar, activo, imagen_thumbnail, imagen_grande)
                VALUES (:titulo, :descripcion, :ubicacion, :provincia, :costo, :categoria_id, :creado_por, :publicar, :activo, :imagen_thumbnail, :imagen_grande)";
    
        try {
            $stmt = $this->conn->prepare($sql);
    
            // Vincular los parámetros
            $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->bindValue(':ubicacion', $ubicacion, PDO::PARAM_STR);
            $stmt->bindValue(':provincia', $provincia, PDO::PARAM_STR);
            $stmt->bindValue(':costo', $costo, PDO::PARAM_STR);
            $stmt->bindValue(':categoria_id', $categoria_id, PDO::PARAM_INT);
            $stmt->bindValue(':creado_por', $creado_por, PDO::PARAM_INT);  
            $stmt->bindValue(':publicar', $publicar, PDO::PARAM_STR);
            $stmt->bindValue(':activo', $activo, PDO::PARAM_STR); 
            $stmt->bindValue(':imagen_thumbnail', $imagen_thumbnail, PDO::PARAM_STR);
            $stmt->bindValue(':imagen_grande', $imagen_grande, PDO::PARAM_STR);
    
            // Ejecutar la sentencia
            if ($stmt->execute()) {
                return true; // Hotel creado con éxito
            } else {
                return false; // Falló la ejecución
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    
    //para actualizar la informacion de los hoteles
    public function actualizar($hotel_id, $titulo, $descripcion, $ubicacion, $provincia, $costo, $categoria_id, $publicar, $activo, $imagen_thumbnail, $imagen_grande) {
        try {
            // Consulta SQL para actualizar los datos de un hotel
            $query = "UPDATE hoteles SET 
                        titulo = :titulo, 
                        descripcion = :descripcion, 
                        ubicacion = :ubicacion, 
                        provincia = :provincia, 
                        costo = :costo, 
                        categoria_id = :categoria_id, 
                        publicar = :publicar, 
                        activo = :activo,  -- Añadir el campo activo
                        imagen_thumbnail = :imagen_thumbnail, 
                        imagen_grande = :imagen_grande
                      WHERE id = :id";
            
            // Preparar la sentencia
            $stmt = $this->conn->prepare($query);
    
            // Vincular los parámetros a la sentencia
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':provincia', $provincia);
            $stmt->bindParam(':costo', $costo);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindParam(':publicar', $publicar);
            $stmt->bindParam(':activo', $activo);
            $stmt->bindParam(':imagen_thumbnail', $imagen_thumbnail);
            $stmt->bindParam(':imagen_grande', $imagen_grande);
            $stmt->bindParam(':id', $hotel_id);
    
            // Ejecutar la consulta
            return $stmt->execute();
        } catch (Exception $e) {
            // Si ocurre un error, mostrar el mensaje de la excepción
            echo "Error al actualizar el hotel: " . $e->getMessage();
            return false;  // Devolver falso si hubo un error
        }
    }
    
    
    public function contarHoteles($search = '', $location = '') {
        try {
            // Consulta SQL para contar el número de hoteles según los criterios de búsqueda
            $query = "SELECT COUNT(*) as total FROM hoteles WHERE titulo LIKE ? AND ubicacion LIKE ?";
            $stmt = $this->conn->prepare($query);
    
            // Ejecutar la consulta con los parámetros de búsqueda
            $stmt->execute(["%$search%", "%$location%"]);
            
            // Devolver el número total de hoteles
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (Exception $e) {
            // Si ocurre un error, mostrar el mensaje de la excepción
            echo "Error al contar los hoteles: " . $e->getMessage();
            return 0;  // Devolver 0 en caso de error
        }
    }
    
    public function buscarHoteles($search = '', $location = '', $limit = 9, $offset = 0) {
        try {
            // Consulta SQL para buscar hoteles según los criterios de búsqueda, limitados por paginación
            $query = "SELECT * FROM hoteles 
                      WHERE publicar = 'S' 
                      AND titulo LIKE ? 
                      AND CONCAT(ubicacion, ' ', provincia) LIKE ? 
                      LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($query);
    
            // Vincular los parámetros de la búsqueda y paginación
            $stmt->bindValue(1, "%$search%");
            $stmt->bindValue(2, "%$location%");
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    
            // Ejecutar la consulta
            $stmt->execute();
    
            // Devolver los resultados de la búsqueda
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Si ocurre un error, mostrar el mensaje de la excepción
            echo "Error al buscar hoteles: " . $e->getMessage();
            return [];  // Devolver un array vacío en caso de error
        }
    }
    
    public function leer() {
        try {
            // Consulta SQL para leer todos los hoteles con detalles de categoría y creador
            $query = "SELECT h.*, c.nombre_categoria, u.nombre_usuario as creador 
                    FROM " . $this->table_name . " h 
                    LEFT JOIN categorias_hoteles c ON h.categoria_id = c.id 
                    LEFT JOIN usuarios u ON h.creado_por = u.id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            // Devolver todos los hoteles
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Si ocurre un error, mostrar el mensaje de la excepción
            echo "Error al leer los hoteles: " . $e->getMessage();
            return [];  // Devolver un array vacío en caso de error
        }
    }
    
    public function eliminar($id) {
        try {
            // Iniciar la transacción
            $this->conn->beginTransaction();
    
            // Eliminar los "likes" relacionados con el hotel
            $query = "DELETE FROM likes WHERE id_hotel = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
    
            // Eliminar las "opiniones" relacionadas con el hotel
            $query = "DELETE FROM opiniones WHERE hotel_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
    
            // Eliminar el hotel de la tabla de hoteles
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
    
            // Confirmar la transacción
            $this->conn->commit();
    
            return true;  // El hotel y sus datos relacionados fueron eliminados con éxito
        } catch (Exception $e) {
            // Si ocurre un error, hacer rollback y mostrar el mensaje de la excepción
            $this->conn->rollBack();
            echo "Error al eliminar el hotel: " . $e->getMessage();
            return false;  // Devolver falso en caso de error
        }
    }
    
    public function buscar($keyword) {
        try {
            // Consulta SQL para buscar hoteles por título o provincia
            $query = "SELECT h.*, c.nombre_categoria, u.nombre_usuario as creador 
                      FROM " . $this->table_name . " h 
                      LEFT JOIN categorias_hoteles c ON h.categoria_id = c.id 
                      LEFT JOIN usuarios u ON h.creado_por = u.id
                      WHERE h.titulo LIKE ? OR h.provincia LIKE ?";
            
            $stmt = $this->conn->prepare($query);
            $keyword = "%{$keyword}%";
            
            // Vincular el parámetro de búsqueda
            $stmt->bindParam(1, $keyword);
            $stmt->bindParam(2, $keyword);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            // Retornar los resultados de la búsqueda
            return $stmt;
        } catch (Exception $e) {
            // Si ocurre un error, mostrar el mensaje de la excepción
            echo "Error al buscar los hoteles: " . $e->getMessage();
            return false;  // Retornar falso en caso de error
        }
    }
    
}