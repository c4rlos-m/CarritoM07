<?php
function innitSession(){
    session_start();
}

function login() {
    $user = getUserFile(); 

    // Obtener los parámetros del formulario GET o POST
    $username = isset($_GET['username']) ? $_GET['username'] : null;
    $password = isset($_GET['password']) ? $_GET['password'] : null;

    if (!empty($username) && !empty($password)) {
        foreach ($user->user as $userData) {
            if ((string)$userData->username === $username && (string)$userData->password === $password) {
                // Iniciar sesión
                $_SESSION['username'] = $username; 
                echo "Usuario logeado correctamente";
                return;
            }
        }
        // Usuario o contraseña incorrectos
        echo "Usuario o contraseña incorrectos";
    } else {
        // Si el nombre de usuario o contraseña están vacíos
        echo "Usuario o contraseña vacíos";
    }
}

function logout() {
    session_destroy();
    echo "Sesión cerrada correctamente";
}

function isLogged() {
    // Verificar si el usuario está logueado
    return isset($_SESSION['username']);
}



?>