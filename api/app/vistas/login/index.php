<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogroFilm</title>
    <link rel="shortcut icon" href="./public/img/logo.png" type="image/x-icon">
    <link href="./public/css/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./public/css/estilos.css">
    <style>
        #contenedor {
            height: 100vh;
            background-image: url('./public/img/bg-inicio.webp');
            background-repeat: no-repeat;
            background-size: cover;
        }

        main {
            width: 25%;
        }

        @media (max-width: 1199.98px) {
            main {
                width: 50%;
            }
        }

        @media (max-width: 575.98px) {
            main {
                width: 100% !important;
            }

        }
    </style>
</head>

<body>
    <div id="contenedor" class="d-flex align-items-center py-4">
        <main class="form-signin m-auto card p-5">
            <form class="text-center" method="post">
                <img class="mb-4" src="./public/img/logo.png" height="100px">
                <h1 class="h3 mb-3 fw-normal">Login</h1>

                <div class="form-floating">
                    <input type="email" class="form-control" id="floatingInput" placeholder="email@email.com" required>
                    <label for="floatingInput">Correo</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                    <label for="floatingPassword">Password</label>
                </div>

                <button class="btn btn-primary w-100 py-2 mt-3" type="submit">Iniciar Sesion</button>
                <p class="text-muted mt-3">¿No tienes cuenta? <a class="text-primary" href="#">Registrate</a></p>
                <p class="mt-5 mb-3 text-body-secondary">© 2023–2024</p>
            </form>
        </main>
    </div>

    <script src="./public/js/bootstrap/bootstrap.bundle.min.js"></script>
</body>

</html>