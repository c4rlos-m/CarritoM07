<?php 
include_once 'components/user/login.php';

function createCartFile($username) {
    $cartFile = 'DB/carts/cart_' . $username . '.xml';
    echo "Creando un nuevo archivo de carrito para $username <br>";
    
    // Crear un nuevo carrito con información por defecto
    $cart = new SimpleXMLElement('<cart></cart>');
    
    // Añadir información por defecto
    $cart->addChild('username', $username);
    $cart->addChild('created_at', date('Y-m-d H:i:s')); // Añadir la fecha de creación

    // Guardar el nuevo carrito en un archivo XML
    $cart->asXML($cartFile);
    
    return $cart; // Retornar el carrito creado si es necesario
}


function getCart() {
    if (!isset($_SESSION['username'])) {
        echo "Debe iniciar sesión para acceder al carrito.";
        return null;
    }

    $username = $_SESSION['username'];
    $cartFile = 'DB/carts/cart_' . $username . '.xml';

    if (!file_exists($cartFile)) {
        $cart = createCartFile($username);
    } else {
        echo "Cargando carrito existente para $username <br>";
        $cart = simplexml_load_file($cartFile);
    }

    return $cart;
}

function saveCart($cart) {
    $cartFile = 'DB/carts/cart_' . $_SESSION['username'] . '.xml';
    
    // Debug: Mostrar que se está guardando el carrito
    echo "Saving cart to $cartFile <br>";
    
    // Guardar el carrito y verificar si se guarda correctamente
    if ($cart->asXML($cartFile)) {
        echo "Carrito guardado correctamente.<br>";
        return true;
    } else {
        echo "Error al guardar el carrito.<br>";
        return false;
    }
}

function addProduct($prod_id, $quantity) {
    $cart = getCart();
    if (!$cart) {
        return; // Si no hay carrito, detener el proceso.
    }

    $price = getItemPrice($prod_id);
    if ($price === null) {
        echo "Error: Producto no encontrado en el catálogo.<br>";
        return;
    }

    // Verificar el stock del producto
    $availableStock = getItemStock($prod_id);
    if ($availableStock === null) {
        echo "Error: No se pudo obtener el stock del producto.<br>";
        return;
    }

    if ($quantity > $availableStock) {
        echo "Error: No hay suficiente stock para añadir esta cantidad. Stock disponible: $availableStock.<br>";
        return;
    }

    // Verificar si el producto ya está en el carrito
    $productFound = false;
    foreach ($cart->item as $item) {
        if ((string)$item->product_id === (string)$prod_id) {
            // Producto encontrado, incrementar la cantidad
            $item->quantity = intval($item->quantity) + $quantity; // Sumar cantidad
            $productFound = true;
            echo "Cantidad del producto actualizada en el carrito. <br>";
            break;
        }
    }

    // Si el producto no existe en el carrito, añadirlo
    if (!$productFound) {
        echo "Añadiendo nuevo producto al carrito.<br>";
        // Crear un nuevo pedido en el carrito
        $item = $cart->addChild('item');
        $item->addChild('product_id', $prod_id);
        $item->addChild('quantity', $quantity);
        $item->addChild('price', $price * $quantity);
    }

    // Restar el stock disponible
    removeStock($prod_id, $quantity);

    // Intentar guardar el carrito
    if (saveCart($cart)) {
        echo "Producto añadido al carrito correctamente <br>";
    } else {
        echo "Error al añadir producto al carrito <br>";
    }
}



function clearCart(){
    if (!isLogged()) {
        echo "Debe iniciar sesión para vaciar el carrito.";
        return;
    }

    $cartFile = 'DB/carts/cart_' . $_SESSION['username'] . '.xml';
    if (file_exists($cartFile)) {
        unlink($cartFile); // Eliminar el archivo de carrito del usuario
        echo "El carrito ha sido vaciado.";
    } else {
        echo "No se encontró el carrito.";
    }
}


function updateCart($prod_id, $quantity) {
    $cart = getCart(); 

    // Variable para verificar si el producto fue encontrado
    $productFound = false;

    // Iterar sobre cada pedido en el carrito
    foreach ($cart->item as $item) {
        if ((string)$item->product_id === (string)$prod_id) {
            // Actualizar la cantidad del producto
            $item->quantity = $quantity;
            echo "Cantidad del producto actualizada correctamente.";
            $productFound = true;
            break;
        }
    }

    // Guardar el carrito actualizado usando saveCart
    if ($productFound) {
        saveCart($cart);
    } else {
        echo "Producto no encontrado en el carrito.";
    }
}



function removeProduct($prod_id, $quantity) {
    $cart = getCart();

    // Variable para verificar si el producto fue encontrado
    $productFound = false;

    // Iterar sobre cada pedido en el carrito
    foreach ($cart->order as $order) {
        if ((string)$order->product_id === (string)$prod_id) {
            // Verificar si la cantidad a eliminar es menor que la cantidad actual
            if ((int)$order->quantity > $quantity) {
                $order->quantity = (int)$order->quantity - $quantity;
                echo "Cantidad del producto actualizada correctamente.";
                $productFound = true;
            } elseif ((int)$order->quantity === $quantity) {
                $dom = dom_import_simplexml($order);
                $dom->parentNode->removeChild($dom);
                echo "Producto eliminado del carrito.";
                $productFound = true;
            } else {
                echo "No se puede eliminar más cantidad de la que hay en el carrito.";
                $productFound = true;
            }
            break;
        }
    }

    // Guardar el carrito actualizado usando saveCart
    if ($productFound) {
        saveCart($cart);
    } else {
        echo "Producto no encontrado en el carrito.";
    }
}


function addToCart($prod_id, $quantity) {
    $cart = getCart();
    if (!$cart) {
        return; // Si no hay carrito, detener el proceso.
    }

    $productExists = false;
    $price = getItemPrice($prod_id);  // Obtener el precio del producto
    foreach ($cart->order as $order) {
        if ((string)$order->product_id === (string)$prod_id) {
            $order->quantity = intval($order->quantity) + $quantity; // Sumar cantidad
            $productExists = true;

            if (saveCart($cart)) {
                echo "Cantidad del producto actualizada en el carrito <br>";
            } else {
                echo "Error al actualizar el carrito <br>";
            }
            return;
        }
    }

    // Si el producto no existe, añadirlo
    if (!$productExists) {
        addProduct($prod_id, $quantity);
    }
}


function removeFromCart($prod_id, $quantity) {
    if (!isLogged()) {
        echo "Debe iniciar sesión para modificar el carrito.";
        return;
    }

    $cart = getCart();
    if (!$cart) {
        return;
    }

    foreach ($cart->order as $order) {
        if ((string)$order->product_id === (string)$prod_id) {
            $order->quantity = intval($order->quantity) - $quantity;

            if ($order->quantity <= 0) {
                $dom = dom_import_simplexml($order);
                $dom->parentNode->removeChild($dom);
                echo "Producto eliminado del carrito correctamente <br>";
            } else {
                echo "Cantidad del producto actualizada correctamente <br>";
            }

            saveCart($cart);
            return;
        }
    }

    echo "Producto no encontrado en el carrito <br>";
}



function viewCart() {
    if (!isLogged()) {
        echo "Debe iniciar sesión para ver el carrito.";
        return;
    }

    $cart = getCart(); // Cargar el carrito del usuario desde su archivo XML
    if (!$cart) {
        return; // Si no hay carrito, detener la ejecución
    }

    if (count($cart->item) == 0) {
        echo "El carrito está vacío.";
        return;
    }

    echo "<h2>Contenido del Carrito:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Producto ID</th><th>Cantidad</th><th>Precio Unitario</th><th>Total</th></tr>";

    $totalCart = 0;

    foreach ($cart->item as $item) {
        $product_id = (string)$item->product_id;  
        $quantity = (int)$item->quantity;         
        
        // Obtener el precio del producto usando la función existente
        $price = getItemPrice($product_id);
        
        // Calcular el total del producto
        $totalPrice = $price * $quantity;          
        $totalCart += $totalPrice;

        echo "<tr>";
        echo "<td>{$product_id}</td>";
        echo "<td>{$quantity}</td>";
        echo "<td>{$price}</td>";
        echo "<td>{$totalPrice}</td>";
        echo "</tr>";
    }

    echo "<tr><td colspan='3' align='right'><strong>Total del Carrito:</strong></td><td>{$totalCart}</td></tr>";
    echo "</table>";

    echo "Procced to checkout <a href='main.php?action=checkout'>Checkout</a>";
}





?>
