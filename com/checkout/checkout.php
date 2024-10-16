<?php

include_once 'com/user/user.php';
include_once 'com/discounts/discounts.php';


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

function shippingCost($total){
    if($total > 50){
        return 0;
    }else{
        return 5;
    }
}

function checkout() {
    if (!isLogged()) {
        echo "Debe iniciar sesión para realizar la compra.";
        return;
    }

    $cart = getCart();
    if (!$cart || empty($cart->items->item)) {
        echo "El carrito está vacío. No se puede proceder al checkout.";
        echo "<br><a href='main.php'>Volver al catálogo</a>";
        return; 
    }
    
    $username = $_SESSION['username'];
    $user = getUserFile();  


    
    $total = 0;
    $currency = "€";  
    $orderDetails = "";  

    echo "<h2>Resumen de la compra para $username:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Producto ID</th><th>Cantidad</th><th>Precio Unitario</th><th>Total Producto</th></tr>";

    
    foreach ($cart->items->item as $item) {
        $prod_id = (string)$item->product_id;
        $quantity = (int)$item->quantity;
        $price = getItemPrice($prod_id);  

        if ($price === null) {
            echo "Error: Producto $prod_id no encontrado en el catálogo.<br>";
            continue;
        }

        
        $totalProduct = $quantity * $price;
        $total += $totalProduct;

        
        echo "<tr>";
        echo "<td>{$prod_id}</td>";
        echo "<td>{$quantity}</td>";
        echo "<td>{$price} {$currency}</td>";
        echo "<td>{$totalProduct} {$currency}</td>";
        echo "</tr>";

        
        
        $orderDetails .= "Producto ID: {$prod_id}, Cantidad: {$quantity}, Total: {$totalProduct} {$currency}<br>";

    }
    
    
    if (isset($_GET['discount'])) {
        $discountCode = $_GET['discount'];
        $discountData = getDiscount($discountCode);
        if ($discountData) {
            
            $discountCode = $discountData['code'];
            $discountPercentage = $discountData['percentage'];

            $discountAmount = $total * $discountPercentage / 100;
            $total -= $discountAmount;

            echo "<tr class='discount-row'><td colspan='3' align='right'>Descuentos: {$discountCode}</td><td class='discount-amount'>-{$discountAmount} {$currency}</td></tr>";
        } else {
            echo "<tr class='error-row'><td colspan='4'>Código de descuento inválido: <strong><span class='discount-code'>{$discountCode}</span></strong></td></tr>";
        }
    }


    echo "<tr><td colspan='3' align='right'><strong>Total:</strong></td><td id='cart-total'>{$total} {$currency}</td></tr>";

    echo "</table>";




    
    foreach ($user->user as $userData) {
        if ((string)$userData->username === $username) {
            $currentBalance = (float)$userData->balance;
            
            if ($currentBalance < $total) {
                echo "<p>Error: Saldo insuficiente. Tu saldo es de {$currentBalance} {$currency}, pero necesitas {$total} {$currency}.</p>";
                return;
            }

            
            $userData->balance = $currentBalance - $total;
            echo "<p>Compra realizada con éxito. Nuevo saldo: " . $userData->balance . " {$currency}</p>";
            break;
        }
    }

    
    if ($user->asXML('DB/users.xml')) {
        echo "<p>Saldo actualizado correctamente.</p>";
    } else {
        echo "<p>Error al actualizar el saldo del usuario.</p>";
    }

    // Mostrar el total de la compra
    // echo "<h3>Total final de la compra: {$total} {$currency}</h3>";

    
    clearCart();

    // Mostrar un resumen final con los productos comprados
    // echo "<h3>Resumen de productos comprados:</h3>";
    // echo $orderDetails;

    header("refresh:30;url=main.php");  
}

?>