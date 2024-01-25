<?php

class UsuarioModelo
{
    private $bd;
    public function __construct()
    {
        $this->bd = new Db();
    }

    public function getUsuarioById($id)
    {
        $this->bd->query('SELECT * FROM usuarios WHERE id_usr = :id_usr');
        $this->bd->bind(':id_usr', $id);
        return $this->bd->registro();
    }

    public function getUsuarioByCorreo($correo)
    {
        $this->bd->query('SELECT * FROM usuarios WHERE correo = :correo');
        $this->bd->bind(':correo', $correo);
        return $this->bd->registro();
    }

    public function getUserByUsername($username)
    {
        $this->bd->query('SELECT * FROM usuarios WHERE username = :username');
        $this->bd->bind(':username', $username);
        return $this->bd->registro();
    }

    public function getAllUsuarios()
    {
        $this->bd->query('SELECT * FROM usuarios');
        return $this->bd->registros();
    }

    public function updateUsuario($id, $datos)
    {
        $id = $datos["id_usr"];
        unset($datos["id_usr"]);

        $columnas = array_keys((array) $datos);
        $setear = "";

        for ($i = 0; $i < count($columnas); $i++) {
            $setear .= $i < count($columnas) - 1 ? $columnas[$i] . " = :" . $columnas[$i] . ", " : $columnas[$i] . " = :" . $columnas[$i];
        }

        $this->bd->query("UPDATE usuarios SET " . $setear . " WHERE id_usr = :id_usr");

        foreach ($datos as $key => $value) {
            $this->bd->bind(":" . $key, $value);
        }
        $this->bd->bind(":id_usr", $id);

        return $this->bd->execute();
    }
    
    public function updateClave($datos)
    {
        $this->bd->query('UPDATE usuarios SET clave = :clave WHERE id_usr = :id_usr');
        $this->bd->bind(':clave', $datos->clave);
        $this->bd->bind(':id_usr', $datos->id_usr);

        return $this->bd->execute();
    }

    public function addUsuario($datos)
    {
        $columnas = implode(', ', array_keys((array) $datos));
        $valores = implode(', :', array_keys((array) $datos));
        $sql = "INSERT INTO " . "USUARIOS" . " (" . $columnas . ") VALUES (:" . $valores . ")";
        $this->bd->query($sql);

        foreach ($datos as $key => $value) {
            $this->bd->bind(":" . $key, $value);
        }

        if ($this->bd->execute()) {
            return  $this->getUsuarioById($this->bd->lastInsertId());
        }
        return  false;
    }

    public function deleteUsuario($id_usuario)
    {
        $this->bd->query('DELETE FROM usuarios WHERE id_usr = :id_usr');
        $this->bd->bind(':id_usr', $id_usuario);
        return $this->bd->execute();
    }

    public function filtrar($datos)
    {
        $condiciones = [];
        $parametros = [];

        foreach ($datos as $clave => $valor) {
            if (empty($valor)) {
                continue;
            }

            if ($clave === "username" || $clave === "correo") {
                $condiciones[] = "(username LIKE :username_val OR correo LIKE :correo_val)";
                $parametros[":username_val"] = "%$valor%";
                $parametros[":correo_val"] = "%$valor%";
                continue;
            }

            if ($clave === "es_admin") {
                $condiciones[] = "$clave = :$clave";
            } else {
                $condiciones[] = "$clave LIKE :$clave";
            }

            // Agregar parámetros para otros campos
            $parametros[":$clave"] = $clave === "es_admin" ? $valor : "%$valor%";
        }

        $sql = 'SELECT * FROM usuarios WHERE ' . implode(' AND ', $condiciones);

        $this->bd->query($sql);

        // Vincular cada valor
        foreach ($parametros as $param => $val) {
            $this->bd->bind($param, $val);
        }

        // Ejecutar y retornar los resultados
        return $this->bd->registros();
    }

    public function getNextId()
    {
        $this->bd->query("SELECT COALESCE(MAX(id_peli), 0) + 1 as id FROM peliculas");
        $result = $this->bd->registro();
        $id = $result;

        return $id;
    }
}
