<?php

require_once("../vendor/autoload.php");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class Token
{

    public function __construct()
    {
    }

    public function generarToken($usuario)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 120 * 60;
        $payload = [
            'id_usr' => $usuario->id_usr,
            'exp' => $expirationTime,
        ];

        $token_jwt = JWT::encode($payload, TOKEN_KEY, 'HS256');
        return $token_jwt;
    }

    public function isLogin()
    {
        $headers = apache_request_headers();
        $token = null;

        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/Bearer (.+)/', $headers['Authorization'], $matches);

            if (isset($matches[1])) {
                $token = $matches[1];
            }
        }

        if (!isset($token)) return false;

        try {
            $decoded = JWT::decode($token, new Key(TOKEN_KEY, 'HS256'));
            $actual = time();
            $timeDecode = $decoded->exp;

            if ($actual > $timeDecode) {
                return false;
            }
        } catch (ExpiredException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
