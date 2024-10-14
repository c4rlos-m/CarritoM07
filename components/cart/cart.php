<?php 
include_once 'components/user/login.php';

function createCartFile($username) {
    $cartFile = 'DB/users/account_' . $username . '.xml';
    echo "Creando un nuevo archivo de carrito para $username <br>";
    
    $cart = new SimpleXMLElement('<account></account>');
    
    $cart->addChild('username', $username);
    $cart->addChild('created_at', date('Y-m-d H:i:s')); 

    $cart->asXML($cartFile);
    
    return $cart; 
}


function getCart() {
    if (!isset($_SESSION['username'])) {
        echo "Debe iniciar sesión para acceder al carrito.";
        return null;
    }

    $username = $_SESSION['username'];
    $cartFile = 'DB/users/account_' . $username . '.xml';

    if (!file_exists($cartFile)) {
        $cart = createCartFile($username);
    } else {
        echo "Cargando carrito existente para $username <br>";
        $cart = simplexml_load_file($cartFile);
    }
    

    return $cart;
}

function saveCart($cart) {
    $cartFile = 'DB/users/account_' . $_SESSION['username'] . '.xml';
    
    echo "Saving cart to $cartFile <br>";
    
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
        return; 
    }

    $price = getItemPrice($prod_id);
    if ($price === null) {
        echo "Error: Producto no encontrado en el catálogo.<br>";
        return;
    }

    $availableStock = getItemStock($prod_id);
    if ($availableStock === null) {
        echo "Error: No se pudo obtener el stock del producto.<br>";
        return;
    }

    if ($quantity > $availableStock) {
        echo "Error: No hay suficiente stock para añadir esta cantidad. Stock disponible: $availableStock.<br>";
        return;
    }

    if (!$cart->items) {
        $cart->addChild('items');
    }

    $productFound = false;
    foreach ($cart->items->item as $item) {
        if ((string)$item->product_id === (string)$prod_id) {
            $item->quantity = intval($item->quantity) + $quantity; 
            $item->price = floatval($item->price) + ($price * $quantity); 
            $productFound = true;
            echo "Cantidad del producto actualizada en el carrito. <br>";
            break;
        }
    }

    if (!$productFound) {
        echo "Añadiendo nuevo producto al carrito.<br>";
        $item = $cart->items->addChild('item');
        $item->addChild('product_id', $prod_id);
        $item->addChild('quantity', $quantity);
        $item->addChild('price', $price * $quantity); 
    }

    removeStock($prod_id, $quantity);

    if (saveCart($cart)) {
        echo "Producto añadido al carrito correctamente <br>";
    } else {
        echo "Error al añadir producto al carrito <br>";
    }
}

function clearCart() {
    if (!isLogged()) {
        echo "Debe iniciar sesión para vaciar el carrito.";
        return;
    }

    $cartFile = 'DB/users/account_' . $_SESSION['username'] . '.xml';

    if (file_exists($cartFile)) {
        $cart = simplexml_load_file($cartFile);

        if (empty($cart->items->item)) {
            echo "El carrito está vacío.";
            return; 
        }

        if (!$cart->purchases) {
            $cart->addChild('purchases');
        }

        $purchase = $cart->purchases->addChild("purchase");
        $purchase->addAttribute("id", uniqid()); 

        foreach ($cart->items->item as $item) {
            $itemNode = $purchase->addChild('item'); 
            $itemNode->addChild('product_id', (string)$item->product_id);
            $itemNode->addChild('quantity', (string)$item->quantity);
            $itemNode->addChild('price', (string)$item->price);
        }

        
        $itemsToRemove = [];
        foreach ($cart->items->item as $item) {
            $itemsToRemove[] = $item; 
        }

        foreach ($itemsToRemove as $item) {
            $dom = dom_import_simplexml($item);
            $dom->parentNode->removeChild($dom); 
        }

        if ($cart->asXML($cartFile)) {
            echo "El carrito ha sido vaciado y los elementos han sido guardados en 'purchases'.";
        } else {
            echo "Error al guardar el carrito.";
        }
    } else {
        echo "No se encontró el carrito.";
    }
}

function emptyCart(){
    if (!isLogged()) {
        echo "Debe iniciar sesión para vaciar el carrito.";
        return;
    }

    $cartFile = 'DB/users/account_' . $_SESSION['username'] . '.xml';

    if (file_exists($cartFile)) {
        $cart = simplexml_load_file($cartFile);

        if (empty($cart->items->item)) {
            echo "El carrito está vacío.";
            return; 
        }
        $itemsToRemove = [];
        foreach ($cart->items->item as $item) {
            $itemsToRemove[] = $item; 
        }

        foreach ($itemsToRemove as $item) {
            $dom = dom_import_simplexml($item);
            $dom->parentNode->removeChild($dom); 
        }

        if ($cart->asXML($cartFile)) {
            echo "Carrito ha sido vaciado con exito.";
        } else {
            echo "Error al guardar el carrito.";
        }

    }
}


function updateCart($prod_id, $quantity) {
    $cart = getCart(); 

    $productFound = false;

    foreach ($cart->items->item as $item) {
        if ((string)$item->product_id === (string)$prod_id) {
            $item->quantity = $quantity;
            echo "Cantidad del producto actualizada correctamente.<br>";
            $productFound = true;
            break;
        }
    }

    if ($productFound) {
        saveCart($cart);
    } else {
        echo "Producto no encontrado en el carrito.";
    }
}



function removeProduct($prod_id, $quantity) {
    $cart = getCart();

    if (!$cart || !isset($cart->items)) {
        echo "Carrito no encontrado.";
        return;
    }

    $productFound = false;

    foreach ($cart->items->item as $item) { // Corregir el acceso al nodo <item>
        if ((string)$item->product_id === (string)$prod_id) {

            if ((int)$item->quantity > $quantity) {
                $item->quantity = (int)$item->quantity - $quantity;
                echo "Cantidad del producto actualizada correctamente.";
                $productFound = true;
            } elseif ((int)$item->quantity === $quantity) {
                $dom = dom_import_simplexml($item);
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

    if ($productFound) {
        saveCart($cart);
    } else {
        echo "Producto no encontrado en el carrito.";
    }
}



function addToCart($prod_id, $quantity) {
    $cart = getCart();
    if (!$cart) {
        return; 
    }

    $productExists = false;
    $price = getItemPrice($prod_id);  
    foreach ($cart->order as $order) {
        if ((string)$order->product_id === (string)$prod_id) {
            $order->quantity = intval($order->quantity) + $quantity; 
            $productExists = true;

            if (saveCart($cart)) {
                echo "Cantidad del producto actualizada en el carrito <br>";
            } else {
                echo "Error al actualizar el carrito <br>";
            }
            return;
        }
    }

    
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
    if (!$cart || !isset($cart->items)) {
        echo "Carrito no encontrado.";
        return;
    }

    foreach ($cart->items->item as $item) { // Corregir el acceso al nodo <item>
        if ((string)$item->product_id === (string)$prod_id) {
            $item->quantity = intval($item->quantity) - $quantity;

            if ($item->quantity <= 0) {
                $dom = dom_import_simplexml($item);
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

    $cart = getCart(); 
    if (!$cart) {
        echo "No se encontró el carrito.";
        return; 
    }

    
    if (empty($cart->items->item)) { 
        echo "El carrito está vacío.";
        return; 
    }

    echo "<h2>Contenido del Carrito:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Producto ID</th><th>Cantidad</th><th>Precio Unitario</th><th>Total</th></tr>";

    $totalCart = 0;

    
    foreach ($cart->items->item as $item) {
        $product_id = (string)$item->product_id;  
        $quantity = (int)$item->quantity;         

        
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

    
    echo "<tr><td colspan='3' align='right'><strong>Total del Carrito:</strong></td><td id='cart-total'>{$totalCart}</td></tr>";
    echo "</table>";

    
    echo "<form id='discount-form' action='main.php' method='GET'>";
    echo "<input type='hidden' name='action' value='checkout'>"; 
    echo "¿Tienes un código de descuento? <input type='text' name='discount' id='discount'><br>";
    echo "<button type='submit'>Proceder al Checkout</button>"; 
    echo "</form>";
}

?>
