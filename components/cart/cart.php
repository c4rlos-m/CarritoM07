<?php 
include_once 'components/user/login.php';

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

function removeProduct($prod_id, $quantity) {
    $cart = getCart(); // Cargar el carrito desde el archivo XML
    $cartFile = 'DB/cart.xml';

    // Variable para verificar si el producto fue encontrado
    $productFound = false;

    // Iterar sobre cada pedido en el carrito
    foreach ($cart->order as $order) {
        if ((string)$order->product_id === (string)$prod_id) {
            // Verificar si la cantidad a eliminar es menor que la cantidad actual
            if ((int)$order->quantity > $quantity) {
                // Reducir la cantidad
                $order->quantity = (int)$order->quantity - $quantity;
                echo "Cantidad del producto actualizada correctamente.";
                $productFound = true; // Marca que se ha encontrado el producto
            } elseif ((int)$order->quantity === $quantity) {
                // Convertir el objeto SimpleXMLElement a DOM y eliminar el nodo
                $dom = dom_import_simplexml($order);
                $dom->parentNode->removeChild($dom);
                echo "Producto eliminado del carrito.";
                $productFound = true; // Marca que se ha encontrado el producto
            } else {
                echo "No se puede eliminar más cantidad de la que hay en el carrito.";
                $productFound = true; // Marca que se ha encontrado el producto
            }
            break; // Salir del bucle una vez que se ha procesado el producto
        }
    }

    // Guardar el carrito actualizado en el archivo XML
    if ($productFound) {
        $cart->asXML($cartFile); // Guardar el carrito actualizado en el archivo XML
    } else {
        echo "Producto no encontrado en el carrito.";
    }
}

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

function removeFromCart($prod_id, $quantity) {
    $cart = getCart();
    $cartFile = 'DB/cart.xml'; 

    foreach ($cart->order as $order) { 
        if ((string)$order->product_id === (string)$prod_id) {
            $order->quantity = intval($order->quantity) - $quantity;
            if ($order->quantity <= 0) { // Si la cantidad es cero o menor
                // Convertir SimpleXMLElement a DOM y eliminar el nodo
                $dom = dom_import_simplexml($order);
                $dom->parentNode->removeChild($dom);
                echo "Producto eliminado del carrito correctamente <br>";
            } else {
                echo "Cantidad del producto actualizada correctamente <br>";
            }
            // Guardar el carrito actualizado
            $cart->asXML($cartFile);
            return;
        }
    }
    echo "Producto no encontrado en el carrito <br>";
}

function viewCart() {
    // Verificar si el usuario está logueado
    if (!isLogged()) {
        echo "Debe iniciar sesión para ver el carrito.";
        return;
    }

    // Cargar el carrito desde el archivo XML
    $cart = getCart(); 

    if (count($cart->order) == 0) {
        echo "El carrito está vacío.";
        return;
    }

    // Mostrar el contenido del carrito en una tabla
    echo "<h2>Contenido del Carrito:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Producto ID</th><th>Cantidad</th><th>Precio</th><th>Moneda</th><th>Total</th></tr>";

    $totalCart = 0;

    // Iterar sobre cada producto en el carrito
    foreach ($cart->order as $order) {
        $product_id = (string)$order->product_id;
        $quantity = (int)$order->quantity;
        $price = (float)$order->product_price->price;
        $currency = (string)$order->product_price->currency;
        $totalPrice = $price * $quantity;
        $totalCart += $totalPrice;

        echo "<tr>";
        echo "<td>{$product_id}</td>";
        echo "<td>{$quantity}</td>";
        echo "<td>{$price}</td>";
        echo "<td>{$currency}</td>";
        echo "<td>{$totalPrice}</td>";
        echo "</tr>";
    }

    echo "<tr><td colspan='4' align='right'><strong>Total del Carrito:</strong></td><td>{$totalCart} {$currency}</td></tr>";
    echo "</table>";
}


?>
