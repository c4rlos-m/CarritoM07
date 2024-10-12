<?php 



function getUserFile(){
    $userFile = 'DB/users.xml'; 

    if (!file_exists($userFile)) {
        $user = new SimpleXMLElement('<users></users>');
        $user->asXML($userFile); 
    } else {
        $user = simplexml_load_file($userFile);
    }

    return $user;
}

function userExists($username){
    $user = getUserFile();

    foreach ($user->user as $user) {
        if ((string)$user->username === $username) {
            return true;
        }
    }
    return false;
}

function addUser($username, $password, $email) {
    $user = getUserFile();

    if(!userExists($username)){

        $newUser = $user->addChild('user');
        $newUser->addChild('id', uniqid());
        $newUser->addChild('username', $username);
        $newUser->addChild('password', $password);
        $newUser->addChild('email', $email);
        $newUser->addChild('balance', 0);
        
        $userFile =  'DB/users.xml'; 
        
        if($user->asXML($userFile)){
            echo "Usuario agregado correctamente ";
        };
    }else{
        echo "Usuario ya existe ";
    }
}

function addBalance($username, $amount){

    $user = getUserFile();

    foreach ($user->user as $userData) {
        if ((string)$userData->username === $username) {
            $userData->balance = (int)$userData->balance + $amount;
            // echo "Saldo actualizado correctamente";
            break;
        }
    }

    if($user->asXML('DB/users.xml')){
        echo "Saldo actualizado correctamente <br>";
    }
}

function getUserID($username){
    $user = getUserFile();

    foreach ($user->user as $user) {
        if ((string)$user->username === $username) {
            // echo "ID del usuario: " . (string)$user->id;
            return (string)$user->id;
        }
    }
    return null;
}

function removeUser($username) {
    $user = getUserFile(); 
    $userFile = 'DB/users.xml'; 

    $userID = getUserID($username); 
    foreach ($user->user as $userData) {
        if ((string)$userData->id === $userID) {
            unset($user->user[0]); 

            if($user->asXML($userFile)) {
                echo "Usuario eliminado correctamente";
                return;
            } 
        }
    }
    echo "Usuario no encontrado";
}



?>