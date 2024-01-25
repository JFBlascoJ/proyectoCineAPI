<?php
class Valoracion extends Controlador
{
    public function __construct()
    {
        $token = new Token();
        if (!$token->isLogin()) {
            header("Content-Type: application/json", true, 401);
            exit;
        }
    }

    // METODOS PUBLICOS
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->addValoracion();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_valoracion'])) {
            $this->getValoracionById($_GET['id_valoracion']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_peli'])) {
            $this->getValoracionByPeli($_GET['id_peli']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_usr'])) {
            $this->getValoracionesByUser($_GET['id_usr']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getValoraciones();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id_valoracion'])) {
            $id = $_GET['id_valoracion'];
            $this->deleteValoracionById($id);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $this->updateValoracion();
            return;
        }

        header('Content-Type: application/json', true, 400);
        echo json_encode(['mensaje' => 'Metodo no permitido']);
    }

    public function pelicula($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getValoracionesByIdPeli($id);
            return;
        }
        header('Content-Type: application/json', true, 400);
        echo json_encode(['mensaje' => 'Metodo no permitido']);
    }
    public function usuario($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getValoracionesByIdUsr($id);
            return;
        }
        header('Content-Type: application/json', true, 400);
        echo json_encode(['mensaje' => 'Metodo no permitido']);
    }

    private function addValoracion()
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $json = file_get_contents('php://input');
        $datos = json_decode($json);
        
        if (!$this->modelo('PeliculaModelo')->getPeliculaById($datos->id_peli)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Pelicula no encontrada']);
            return;
        };
        if (!$this->modelo('UsuarioModelo')->getUsuarioById($datos->id_usr)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Usuario no encontrado']);
            return;
        };

        $valoracion = $this->getValoracionByPeliYUser($datos->id_peli, $datos->id_usr);

        if (!$valoracion) {
            if (!$valoracionModelo->addValoracion($datos)) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['mensaje' => 'Error al agregar valoración']);
                return;
            }
    
            header('Content-Type: application/json', true, 201);
            echo json_encode(['mensaje' => 'Comentario agregado']);
        } else {
            if (!$valoracionModelo->updateValoracion($valoracion->id_valoracion, $datos->valoracion)) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['mensaje' => 'Error al actualizar valoración']);
                return;
            }
    
            header('Content-Type: application/json', true, 201);
            echo json_encode(['mensaje' => 'Valoración actualizada']);
        }
        return;
    }

    private function getValoracionById($id)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $Valoracion = $valoracionModelo->getValoracionById($id);
        if ($Valoracion) {
            header('Content-Type: application/json');
            echo json_encode($Valoracion);
            return;
        }
        header('Content-Type: application/json', true, 404);
        echo json_encode(['mensaje' => 'Valoracion no encontrada']);
    }

    private function getValoracionByPeli($id)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $Valoracion = $valoracionModelo->getValoracionByPeli($id);
        if ($Valoracion) {
            header('Content-Type: application/json');
            echo json_encode($Valoracion);
            return;
        }
        header('Content-Type: application/json', true, 404);
        echo json_encode(['mensaje' => 'Valoracion no encontrada']);
    }
    
    private function getValoracionByPeliYUser($id_peli, $id_usr)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        return $valoracionModelo->getValoracionByPeliYUser($id_peli, $id_usr);        
    }
    private function updateValoracion()
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $json = file_get_contents('php://input');
        $datos = json_decode($json);
        // if (!$this->isValidValoracion($datos)) {
        //     header('Content-Type: application/json', true, 400);
        //     echo json_encode(['mensaje' => 'Datos incompletos']);
        //     return;
        // }
        if (!$valoracionModelo->getValoracionById($datos->id_valoracion)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Valoracion no encontrada']);
            return;
        }
        $valoracion = $valoracionModelo->updateValoracion($datos->id_valoracion, $datos->valoracion);
        if ($valoracion) {
            header('Content-Type: application/json', true, 200);
            echo json_encode(['mensaje' => 'Valoracion actualizada']);
        } else {
            header('Content-Type: application/json', true, 200);
            echo json_encode(['mensaje' => 'No se pudo actualizar la valoración']);
        }
        return;
    }

    private function deleteValoracionById($id)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        if (!$valoracionModelo->getValoracionById($id)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Valoracion no encontrada']);
            return;
        }
        $valoracionModelo->deleteValoracionById($id);
        header('Content-Type: application/json', true, 200);
        echo json_encode(['mensaje' => 'Valoracion eliminada']);
    }


    // METODOS PRIVADOS 
    private function getValoraciones()
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $Valoraciones = $valoracionModelo->getValoraciones();
        header('Content-Type: application/json');
        echo json_encode($Valoraciones);
    }
    private function getValoracionesById($id)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $Valoraciones = $valoracionModelo->getValoracionesById($id);
        header('Content-Type: application/json');
        echo json_encode($Valoraciones);
    }
    private function getValoracionesByIdUsr($id)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $Valoraciones = $valoracionModelo->getValoracionesByIdUsr($id);
        header('Content-Type: application/json');
        echo json_encode($Valoraciones);
    }
    private function getValoracionesByIdPeli($id)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $Valoraciones = $valoracionModelo->getValoracionesByIdPeli($id);
        header('Content-Type: application/json');
        echo json_encode($Valoraciones);
    }

    private function getValoracionesByUser($id_usr)
    {
        $valoracionModelo = $this->modelo('ValoracionModelo');
        $valoraciones = $valoracionModelo->getvaloracionesByUser($id_usr);

        header('Content-Type: application/json', true, 200);
        echo json_encode($valoraciones);
        return;
    }

    private function isValidValoracion($datos)
    {
        if (isset($datos->id_usr) && isset($datos->id_peli) && isset($datos->valoracion)) {
            return true;
        }
        return false;
    }
}
