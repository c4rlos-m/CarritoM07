<?php
function innitSession(){
    session_start();
}

function login($username, $password) {
    $user = getUserFile(); 

   
        foreach ($user->user as $userData) {
            if ((string)$userData->username === $username && (string)$userData->password === $password) {
                // Iniciar sesión
                $_SESSION['username'] = $username; 
                echo "Usuario logeado correctamente";
                return;
            }
        }
        echo "Usuario o contraseña incorrectos";
    
}

function logout() {
    session_destroy();
    echo "Sesión cerrada correctamente";
}

function isLogged() {
    
    return isset($_SESSION['username']);
}



?>