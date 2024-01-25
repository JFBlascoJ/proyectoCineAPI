<?php

class Usuario extends Controlador
{

    public function __construct()
    {
    }

    // METODOS PUBLICOS
    public function index()
    {
        $token = new Token();
        if (!$token->isLogin()) {
            header("Content-Type: application/json", true, 401);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->addUsuario();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_usr'])) {
            $id = $_GET['id_usr'] ?? null;
            $this->getUsuarioById($id);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filtrar'])) {
            $this->filtrar();
            return;
        }

        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['nextid'])) {
            $this->getNetxId();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getAllUsuarios();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $this->updateUsuario();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $id = $_GET['id_usr'] ?? null;
            $this->deleteUsuario($id);
        }
    }
    public function registro()
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');

        // Coger datos del body
        // $json = file_get_contents('php://input');
        // $data = json_decode($json);
        $data = $_POST;

        if ($data === 'null') {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Faltan datos para insertar el usuario']);
            return;
        }

        // if (!isset($data->correo) || !isset($data->username) || !isset($data->clave) || !isset($data->nombre) || !isset($data->apellidos)) {
        //     header('Content-Type: application/json', true, 400);
        //     echo json_encode(['mensaje' => 'Faltan datos para insertar el usuario']);
        //     return;
        // }

        if (!isset($data["foto"])) {
            $data["foto"] = null;
        }

        if (!isset($data["es_admin"])) {
            $data["es_admin"] = false;
        }

        if ($usuarioModelo->getUsuarioByCorreo($data["correo"])) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'El correo ya existe']);
            return;
        }

        if ($usuarioModelo->getUserByUsername($data["username"])) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'El usuario ya existe']);
            return;
        }


        $usario = $usuarioModelo->addUsuario($data);
        if (!$usario) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Error al insertar el usuario']);
            return;
        }

        $token = new Token();
        $datos = [
            'token' => $token->generarToken($usario),
            'usuario' => $usario
        ];
        header('Content-Type: application/json', true, 201);
        echo json_encode($datos);
        return;
    }
   
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Metodo no permitido']);
            return;
        }
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        if ($data === 'null') {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'No se ha podido iniciar sesion']);
            return;
        }

        if (!isset($data->correo) || !isset($data->clave)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'No se ha podido iniciar sesion']);
            return;
        }

        $usuarioModelo = $this->modelo('UsuarioModelo');
        $usuario = $usuarioModelo->getUsuarioByCorreo($data->correo);
        if ($usuario === null) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'No se ha podido iniciar sesion']);
            return;
        }
        

        if ($usuario->clave === md5($data->clave)) {
            header('Content-Type: application/json', true, 200);
            $token = new Token();
            $data = [
                'token' => $token->generarToken($usuario),
                'usuario' => $usuario
            ];
            echo json_encode($data);
            return;
        }

        header('Content-Type: application/json', true, 400);
        echo json_encode(['mensaje' => 'No se ha podido iniciar sesion']);
        return;
    }

    public function correo($correo = null)
    {
        if ($_SERVER['REQUEST_METHOD']  !== 'GET' || !isset($correo)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Metodo no permitido']);
            return;
        }

        $this->getUsuarioByCorreo($correo);
    }
    public function username($username = null)
    {
        if ($_SERVER['REQUEST_METHOD']  !== 'GET' || !isset($username)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Metodo no permitido']);
            return;
        }

        $this->getUsuarioByUsername($username);
    }

    // METODOS PRIVADOS
    private function addUsuario()
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');

        $data = $_POST;

        if ($data === 'null') {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Faltan datos para insertar el usuario']);
            return;
        }

        if ($data["clave"] !== $data["clave2"]) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Las contrseñas no coinciden']);

        }

        // if (!isset($data->correo) || !isset($data->username) || !isset($data->nombre) || !isset($data->apellidos)) {
        //     header('Content-Type: application/json', true, 400);
        //     echo json_encode(['mensaje' => 'Faltan datos para insertar el usuario']);
        //     return;
        // }

        if (!isset($data["foto"])) {
            $data["foto"] = null;
        }

        if (!isset($data["es_admin"])) {
            $data["es_admin"] = false;
        }

        if (!isset($data["es_admin"]) && $data["es_admin"]) {
            $data["clave"] = password_hash("P@ssw0rd", PASSWORD_DEFAULT);
        }

        if ($usuarioModelo->getUsuarioByCorreo($data["correo"])) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'El correo ya existe']);
            return;
        }

        if ($usuarioModelo->getUserByUsername($data["username"])) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'El usuario ya existe']);
            return;
        }


        $usario = $usuarioModelo->addUsuario($data);
        if (!$usario) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Error al insertar el usuario']);
            return;
        }

        header('Content-Type: application/json', true, 201);
        echo json_encode($usario);
        return;
    }
    private function getAllUsuarios()
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');
        $usuarios = $usuarioModelo->getAllUsuarios();

        header('Content-Type: application/json', true, 200);
        echo json_encode($usuarios);
    }
    private function getUsuarioByCorreo($correo)
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');
        $usuario = $usuarioModelo->getUsuarioByCorreo($correo);

        if (!$usuario) {
            header('Content-Type: application/json', true, 404);
            echo json_encode([
                'mensaje' => 'No se encontro el usuario'
            ]);
            return;
        }
        header('Content-Type: application/json', true, 200);
        echo json_encode($usuario);
    }

    private function getUsuarioByUsername($username)
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');
        $usuario = $usuarioModelo->getUserByUsername($username);
        if (!$usuario) {
            header('Content-Type: application/json', true, 404);
            echo json_encode([
                'mensaje' => 'No se encontro el usuario'
            ]);
            return;
        }
        header('Content-Type: application/json', true, 200);
        echo json_encode($usuario);
    }

    private function getUsuarioById($id)
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');
        $usuario = $usuarioModelo->getUsuarioById($id);

        if (!$usuario) {
            header('Content-Type: application/json', true, 404);
            echo json_encode(['mensaje' => 'El usuario no existe']);
            return;
        }

        header('Content-Type: application/json', true, 200);
        echo json_encode($usuario);
    }
    private function updateUsuario()
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');

        // Coger datos del body
        $json = file_get_contents('php://input');
        parse_str($json, $data);
        $id = intval($data["id_usr"]);

        if ($data === 'null') {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Faltan datos para actualizar el usuario']);
            return;
        }

        if (!isset($data["id_usr"])) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Faltan datos para actualizar el usuario']);
            return;
        }

        $user = $usuarioModelo->getUsuarioById($data["id_usr"]);
        if (!$user) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'El usuario no existe']);
            return;
        }

        if (!isset($data["foto"]) && $user->foto === null) {
            $data["foto"] = null;
        }


        if (!isset($data["es_admin"])) {
            $data["es_admin"] = false;
        }

        if (isset($data["correo"])) {
            $userCorreo = $usuarioModelo->getUsuarioByCorreo($data["correo"]);
            if ($userCorreo) {
                if ($userCorreo->id_usr . "" != $data["id_usr"]) {
                    header('Content-Type: application/json', true, 400);
                    echo json_encode(['mensaje' => 'El correo ya esta en uso']);
                    return;
                }
            }
        }

        if (isset($data["username"])) {
            $userUsername = $usuarioModelo->getUserByUsername($data["username"]);
            if ($userUsername) {
                if ($userUsername->id_usr != $data["id_usr"]) {
                    header('Content-Type: application/json', true, 400);
                    echo json_encode(['mensaje' => 'El usuario ya existe']);
                    return;
                }
            }
        }


        $usario = $usuarioModelo->updateUsuario($id, $data);
        if (!$usario) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Error al actualizar el usuario']);
            return;
        }
        if (isset($data["clave"])) {
            $usario = $usuarioModelo->updateClave($data);
            if (!$usario) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['mensaje' => 'Error al actualizar el usuario']);
                return;
            }
        }


        header('Content-Type: application/json', true, 200);
        echo json_encode($usario);
        return;
    }

    private function deleteUsuario($id)
    {
        $usuarioModelo = $this->modelo('UsuarioModelo');

        $usuario = $usuarioModelo->getUsuarioById($id);
        if (!$usuario) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Usuario no encontrado']);
            return;
        }

        $usario = $usuarioModelo->deleteUsuario($id);
        if (!$usario) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['mensaje' => 'Error al borra el usuario']);
            return;
        }

        header('Content-Type: application/json', true, 200);
        echo json_encode(['mensaje' => 'Usuario borrado correctamente']);
        return;
    }

    private function filtrar()
    {
        unset($_GET["url"]);
        unset($_GET["filtrar"]);
        $usuarioModelo = $this->modelo('UsuarioModelo');
        $resul = $usuarioModelo->filtrar($_GET);
        header('Content-Type: application/json', true, 200);
        echo json_encode($resul);
        return;
    }

    private function getNetxId() {
        $usuarioModelo = $this->modelo('UsuarioModelo');
        header('Content-Type: application/json', true, 200);
        $resul = $usuarioModelo->getNextId();
        echo json_encode($resul);
    }
}
