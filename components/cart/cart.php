<?php 

function getCart() {
    $cartFile = 'DB/cart.xml'; 

    if (!file_exists($cartFile)) {
        $cart = new SimpleXMLElement('<cart></cart>');
        $cart->asXML($cartFile); 
    } else {
        $cart = simplexml_load_file($cartFile);
    }

    return $cart;
}

function addProduct($prod_id, $quantity, $price, $currency){
    $cart = getCart();

    $order = $cart->addChild('order');
        $order->addChild('product_id', $prod_id);
        $order->addChild('quantity', $quantity);
        $prod_price = $order->addChild('product_price');
        $prod_price->addChild('price', $price);
        $prod_price->addChild('currency', $currency);
        if($cart->asXML('DB/cart.xml')){
            echo "Producto añadido al carrito correctamente <br>";
        }
}

function removeProduct($prod_id){
    $cart = getCart();
    $cartFile =  'DB/cart.xml'; 

    foreach ($cart->order as $order) {
        if ((string)$order->product_id === $prod_id) {
            unset($order[0]);
            $cart->asXML($cartFile);
            echo "Producto eliminado del carrito correctamente <br>";
            return;
        }
    }
    if($cart->asXML($cartFile)){
        echo "Producto no encontrado en el carrito <br>";
    }
    
}


/* AÑADIR AL CARRITO */
function addToCart($prod_id, $quantity, $price, $currency) {
    $cart = getCart();
    $productExists = false; 

    foreach ($cart->order as $order) {
        if ((string)$order->product_id === (string)$prod_id) {
            // Si el producto ya existe, actualizar la cantidad
            $order->quantity = intval($order->quantity) + $quantity;
            $productExists = true;
            // Guardar el carrito actualizado
            if ($cart->asXML('DB/cart.xml')) {
                echo "Cantidad del producto actualizada en el carrito <br>";
            } else {
                echo "Error al actualizar el carrito <br>";
            }
            return; // Salir de la función después de actualizar
        }
    }
    if (!$productExists) {
        addProduct($prod_id, $quantity, $price, $currency);
    }    
}



function removeFromCart($prod_id, $quantity){
    $cart = getCart();
    $cartFile =  'DB/cart.xml'; 

    foreach ($cart->order as $order) {
        if ((string)$order->product_id === $prod_id) {
            $order->quantity = intval($order->quantity) - $quantity;
            if($order->quantity == 0){
                unset($order[0]);
            }
            $cart->asXML($cartFile);
            echo "Producto eliminado del carrito correctamente <br>";
            return;
        }
    }
    echo "Producto no encontrado en el carrito <br>";
}


?>