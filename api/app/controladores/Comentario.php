<?php

class Comentario extends Controlador
{
    public function __construct()
    {
        $token = new Token();
        if (!$token->isLogin()) {
            header("Content-Type: application/json", true, 401);
            exit;
        }
    }
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->addComentario();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_comentario'])) {
            $this->getComentarioById($_GET['id_comentario']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_peli']) && isset($_GET['id_usr'])) {
            $this->getComentarioByPeliYUser($_GET['id_peli'], $_GET['id_usr'], true);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_peli'])) {
            $this->getComentariosByPelicula($_GET['id_peli']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_usr'])) {
            $this->getComentariosByUser($_GET['id_usr']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getComentarios();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id_comentario'])) {
            $id = $_GET['id_comentario'];
            $this->deleteComentarioById($id);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $this->updateComentario();
            return;
        }

        header('Content-Type: application/json', true, 400);
        echo json_encode(['mensaje' => 'Metodo no permitido']);
    }

    private function addComentario()
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        $json = file_get_contents('php://input');
        $datos = json_decode($json);


        if (!$this->isValidComentario($datos)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Datos incompletos']);
            return;
        }

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

        $comentario = $this->getComentarioByPeliYUser($datos->id_peli, $datos->id_usr);

        if (!$comentario) {
            $resul = $comentarioModelo->addComentario($datos);
            if (!$resul) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['mensaje' => 'Error al agregar comentario']);
                return;
            }

            header('Content-Type: application/json', true, 201);
            echo json_encode($resul);
        } else {
            $resul = $comentarioModelo->updateComentario($comentario->id_comentario, $datos->comentario);
            if (!$resul) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['mensaje' => 'Error al actualizar comentario']);
                return;
            }

            header('Content-Type: application/json', true, 201);
            echo json_encode($resul);
        }

        return;
    }
    private function updateComentario()
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        $json = file_get_contents('php://input');
        $datos = json_decode($json);

        if($comentarioModelo->updateComentario($datos->id_comentario, $datos->comentario)) {
            header('Content-Type: application/json', true, 201);
            echo json_encode(['mensaje' => 'Comentario actualizado']);
            return;
        }

        
        header('Content-Type: application/json', true, 400);
        echo json_encode(['mensaje' => 'Datos incompletos']);
        return;
    }

    private function getComentarioById($id)
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        $comentario = $comentarioModelo->getComentarioById($id);

        if (!$comentario) {
            header('Content-Type: application/json', true, 404);
            echo json_encode(['mensaje' => 'Comentario no encontrado']);
            return;
        }

        header('Content-Type: application/json', true, 200);
        echo json_encode($comentario);
        return;
    }

    private function getComentarioByPeliYUser($id_peli, $id_usr, $consultar = false)
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        $comentario = $comentarioModelo->getComentarioByPeliYUser($id_peli, $id_usr);

        if (!$consultar)
            return $comentario;

        header('Content-Type: application/json', true, 200);
        echo json_encode($comentario);
        return;
    }

    private function getComentarios()
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        $comentarios = $comentarioModelo->getComentarios();

        header('Content-Type: application/json', true, 200);
        echo json_encode($comentarios);
        return;
    }
    private function deleteComentarioById($id)
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        if (!$comentarioModelo->getComentarioById($id)) {
            header('Content-Type: application/json', true, 404);
            echo json_encode(['mensaje' => 'Comentario no encontrado']);
            return;
        }

        if (!$comentarioModelo->deleteComentarioById($id)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Error al eliminar comentario']);
            return;
        }
        header('Content-Type: application/json', true, 200);
        echo json_encode(['mensaje' => 'Comentario eliminado']);
        return;
    }

    private function isValidComentario($datos)
    {
        if (!isset($datos->id_peli) || !isset($datos->id_usr) || !isset($datos->comentario)) {
            return false;
        }

        return true;
    }

    private function getComentariosByPelicula($id_peli)
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        $comentarios = $comentarioModelo->getComentariosByPelicula($id_peli);

        header('Content-Type: application/json', true, 200);
        echo json_encode($comentarios);
        return;
    }

    private function getComentariosByUser($id_usr)
    {
        $comentarioModelo = $this->modelo('ComentarioModelo');
        $comentarios = $comentarioModelo->getComentariosByUser($id_usr);

        header('Content-Type: application/json', true, 200);
        echo json_encode($comentarios);
        return;
    }
}
