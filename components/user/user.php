<?php 

function getUserFile(){
    $userFile = 'DB/users.xml'; 

    if (!file_exists($userFile)) {
        $user = new SimpleXMLElement('<user></user>');
        $user->asXML($userFile); 
    } else {
        $user = simplexml_load_file($userFile);
    }

    return $user;
}


function addUser($username, $password, $email) {
    $user = getUserFile();

    $newUser = $user->addChild('user');
    $newUser->addChild('id', uniqid());
    $newUser->addChild('username', $username);
    $newUser->addChild('password', $password);
    $newUser->addChild('email', $email);

    $userFile =  'DB/users.xml'; 
    
    if($user->asXML($userFile)){
        echo "Usuario agregado correctamente ";
    };


}

?>