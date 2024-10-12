<?php

include_once 'components/user/user.php';

function getUserBalance($username){
    $user = getUserFile();

    foreach ($user->user as $user) {
        if ((string)$user->username === $username) {
            // echo "Saldo del usuario: " . (string)$user->balance;
            return (string)$user->balance;
        }
    }
    return null;
}


function checkout(){
    if (!isLogged()) {
        echo "Debe iniciar sesión para realizar la compra.";
        return;
    }

    $cart = getCart();
    if (!$cart) {
        echo "El carrito está vacío.";
        return;
    }

    $total = 0;
    $username = $_SESSION['username'];
    $user = getUserFile();

    foreach ($cart->order as $order) {
        $prod_id = (string)$order->product_id;
        $quantity = (int)$order->quantity;
        $price = (float)$order->price;
        $currency = (string)$order->currency;

        $total += $quantity * $price;

        foreach ($user->user as $userData) {
            if ((string)$userData->username === $username) {
                $userData->balance = (float)$userData->balance - $total;
                break;
            }
        }

        echo "Compra realizada con éxito. Total: $total $currency <br>";
    }

    if($user->asXML('DB/users.xml')){
        echo "Saldo actualizado correctamente <br>";
    }

    clearCart();
}
?>