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

function clearCart(){
    $cartFile = 'DB/cart.xml';
    $cart = new SimpleXMLElement('<cart></cart>');
    $cart->asXML($cartFile);
    echo "Carrito limpiado correctamente";
}

function checkout(){
    if(isLogged()){
        $cart = getCart();
        $user = getUserFile();
        $username = $_SESSION['username'];
        $userID = getUserID($username);
        $userBalance = getUserBalance($username);
        $total = 0;

        foreach ($cart->order as $order) {
            $prod_id = (string)$order->product_id;
            $quantity = (int)$order->quantity;
            $price = (float)$order->product_price->price;
            $currency = (string)$order->product_price->currency;
            $total += $price * $quantity;
        }

        if($total <= $userBalance){
            foreach ($cart->order as $order) {
                $prod_id = (string)$order->product_id;
                $quantity = (int)$order->quantity;
                $price = (float)$order->product_price->price;
                $currency = (string)$order->product_price->currency;

                $userBalance -= $price * $quantity;

                $order = $user->addChild('order');
                $order->addChild('product_id', $prod_id);
                $order->addChild('quantity', $quantity);
                $prod_price = $order->addChild('product_price');
                $prod_price->addChild('price', $price);
                $prod_price->addChild('currency', $currency);
            }

            $user->asXML('DB/users.xml');
            echo "Compra realizada correctamente";
        } else {
            echo "Saldo insuficiente";
        }
    } else {
        echo "Usuario no logeado";
    }
}
?>