<?php

class PeliculaModelo
{
    private $bd;
    public function __construct()
    {
        $this->bd = new Db();
    }

    public function addPelicula($datos)
    {
        $columnas = implode(', ', array_keys((array) $datos));
        $valores = implode(', :', array_keys((array) $datos));
        $sql = "INSERT INTO " . "PELICULAS" . " (" . $columnas . ") VALUES (:" . $valores . ")";
        $this->bd->query($sql);

        foreach ($datos as $key => $value) {
            $this->bd->bind(":" . $key, $value);
        }

        if ($this->bd->execute()) {
            return  $this->getPeliculaById($this->bd->lastInsertId());
        }
        return  false;
    }

    public function getAllPeliculas()
    {
        // $this->bd->query('SELECT * FROM peliculas');
        // $this->bd->query('SELECT peliculas.*, COUNT(comentarios.id_peli) as comentarios 
        // FROM peliculas 
        // LEFT JOIN comentarios ON peliculas.id_peli = comentarios.id_peli
        // GROUP BY peliculas.id_peli');
        $this->bd->query('SELECT peliculas.*, 
        COUNT(comentarios.id_peli) as comentarios, 
        COALESCE(ROUND(AVG(valoraciones.valoracion) / 2, 1), 0) AS valoracion
        FROM peliculas 
        LEFT JOIN comentarios ON peliculas.id_peli = comentarios.id_peli
        LEFT JOIN valoraciones ON peliculas.id_peli = valoraciones.id_peli
        GROUP BY peliculas.id_peli');
        return $this->bd->registros();
    }

    public function getPeliculaById($id)
    {
        $this->bd->query('SELECT peliculas.*, ROUND(AVG(valoraciones.valoracion) / 2, 1) AS valoracion
        FROM peliculas
        LEFT JOIN valoraciones ON peliculas.id_peli = valoraciones.id_peli
        WHERE peliculas.id_peli = :id_peli
        GROUP BY peliculas.id_peli');
        $this->bd->bind(':id_peli', $id);
        return $this->bd->registro();
    }

    public function getPeliculaByTitulo($titulo)
    {
        $titulo = trim($titulo);
        $titulo = "%$titulo%";
        $this->bd->query('SELECT * FROM peliculas WHERE tit_original LIKE :tit_original OR tit_espanol LIKE :tit_espanol');

        $this->bd->bind(':tit_original', $titulo);
        $this->bd->bind(':tit_espanol', $titulo);
        return $this->bd->registros();
    }

    public function getPeliculaByGenero($genero)
    {
        $genero = trim($genero);
        $genero = "%$genero%";
        $this->bd->query('SELECT * FROM peliculas WHERE genero LIKE :genero');

        $this->bd->bind(':genero', $genero);
        return $this->bd->registros();
    }

    public function getPeliculaByAno($ano)
    {
        $ano = trim($ano);
        $this->bd->query('SELECT * FROM peliculas WHERE ano LIKE :ano');

        $this->bd->bind(':ano', $ano);
        return $this->bd->registros();
    }

    public function getPeliculaByDuracion($duracion)
    {
        $duracion = trim($duracion);
        $this->bd->query('SELECT * FROM peliculas WHERE duracion >= :duracion');

        $this->bd->bind(':duracion', $duracion);
        return $this->bd->registros();
    }

    public function updatePelicula($id, $datos)
    {
        $columnas = array_keys((array) $datos);
        $setear = "";

        for ($i = 0; $i < count($columnas); $i++) {
            $setear .= $i < count($columnas) - 1 ? $columnas[$i] . " = :" . $columnas[$i] . ", " : $columnas[$i] . " = :" . $columnas[$i];
        }

        $this->bd->query("UPDATE peliculas SET " . $setear . " WHERE id_peli = :id_peli");

        foreach ($datos as $key => $value) {
            $this->bd->bind(":" . $key, $value);
        }
        $this->bd->bind(":id_peli", $id);

        return $this->bd->execute();
    }

    // public function updatePelicula($id, $datos)
    // {
    //     $columnas = array_keys((array) $datos);
    //     $setear = "";

    //     for ($i = 0; $i < count($columnas); $i++) {
    //         $setear .= $i < count($columnas) - 1 ? $columnas[$i] . " = :" . $columnas[$i] . ", " : $columnas[$i] . " = :" . $columnas[$i];
    //     }

    //     $this->bd->query("UPDATE peliculas SET " . $setear . " WHERE id_peli = :id_peli");

    //     foreach ($datos as $key => $value) {
    //         $this->bd->bind(":" . $key, $value);
    //     }
    //     $this->bd->bind(":id_peli", $id);

    //     return $this->bd->execute();
    // }
    public function deletePelicula($id)
    {
        $this->bd->query('DELETE FROM peliculas WHERE id_peli = :id_peli');
        $this->bd->bind(':id_peli', $id);
        return $this->bd->execute();
    }

    public function getGeneros()
    {
        $this->bd->query('SELECT DISTINCT genero FROM peliculas WHERE genero IS NOT NULL');
        return $this->bd->registros();
    }

    public function filtrar($datos)
    {
        $noRangos = [];
        $rangos = [];
        $condiciones = [];

        foreach ($datos as $clave => $valor) {
            if (strpos(strtolower($clave), "desde") !== false) {
                $baseKey = substr($clave, 0, strpos(strtolower($clave), "desde"));
                $hastaKey = $baseKey . "Hasta";
                $hastaValor = isset($datos[$hastaKey]) ? $datos[$hastaKey] : 9999;

                // Asegurarse de no sobrescribir si ya existe
                if (!isset($rangos[$baseKey])) {
                    $rangos[$baseKey] = ["desde" => $valor, "hasta" => $hastaValor];
                }
            } elseif (strpos(strtolower($clave), "hasta") !== false) {
                $baseKey = substr($clave, 0, strpos(strtolower($clave), "hasta"));
                // Si no se ha procesado "desde" para esta clave
                if (!isset($rangos[$baseKey])) {
                    $desdeKey = $baseKey . "Desde";
                    $desdeValor = isset($datos[$desdeKey]) ? $datos[$desdeKey] : 0;

                    $rangos[$baseKey] = ["desde" => $desdeValor, "hasta" => $valor];
                }
            } else
                $noRangos[$clave] = $valor;
        }

        foreach ($noRangos as $clave => $valor) {
            if ($clave == 'titulo') {
                $condiciones[] = "(tit_espanol LIKE '%$valor%' OR tit_original LIKE '%$valor%')";
            } else {
                $condiciones[] = "$clave = '$valor'";
            }
        }

        foreach ($rangos as $clave => $valor) {
            $condiciones[] = "$clave BETWEEN '{$valor['desde']}' AND '{$valor['hasta']}'";
        }

        $sql = 'SELECT * FROM peliculas WHERE ' . implode(' AND ', $condiciones);

        // Ejecutar la consulta
        $this->bd->query($sql);
        return $this->bd->registros();
    }

    public function getNextId()
    {
        $this->bd->query("SELECT COALESCE(MAX(id_peli), 0) + 1 as id FROM peliculas");
        $result = $this->bd->registro();
        $id = $result;

        return $id;
    }

    public function getTop10() {
        $mysqli = new mysqli("localhost", "root", "", "logrofilm"); // Reemplaza con tus propios datos de conexión
        
        // Verificar la conexión
        if ($mysqli->connect_error) {
            die("Error de conexión a la base de datos: " . $mysqli->connect_error);
        }
        
        // Consulta SQL
        $sql = "SELECT 
                    p.id_peli,
                    p.tit_espanol,
                    p.poster,
                    COALESCE(c.num_comentarios, 0) + COALESCE(v.num_valoraciones, 0) AS suma_total
                FROM 
                    peliculas p
                LEFT JOIN 
                    (SELECT id_peli, COUNT(id_comentario) AS num_comentarios
                     FROM comentarios
                     WHERE fecha >= NOW() - INTERVAL 2 WEEK
                     GROUP BY id_peli) c ON p.id_peli = c.id_peli
                LEFT JOIN 
                    (SELECT id_peli, COUNT(id_valoracion) AS num_valoraciones
                     FROM valoraciones
                     WHERE fecha >= NOW() - INTERVAL 2 WEEK
                     GROUP BY id_peli) v ON p.id_peli = v.id_peli
                ORDER BY 
                    suma_total DESC
                LIMIT 10";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->execute();
            
            $stmt->bind_result($id_peli, $tit_espanol, $poster, $suma_total);
            
            $top10 = array();
            
            while ($stmt->fetch()) {
                $movie = array(
                    'id_peli' => $id_peli,
                    'tit_espanol' => $tit_espanol,
                    'poster' => $poster,
                    'suma_total' => $suma_total
                );
                $top10[] = $movie;
            }
            $stmt->close();
            $mysqli->close();
            return $top10;
        } else {
            die("Error en la consulta: " . $mysqli->error);
        }
    }
}
