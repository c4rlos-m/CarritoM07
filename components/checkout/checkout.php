<?php

include_once 'components/user/user.php';
include_once 'components/discounts/discounts.php';


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


function checkout() {
    if (!isLogged()) {
        echo "Debe iniciar sesión para realizar la compra.";
        return;
    }
    $username = $_SESSION['username'];
    $user = getUserFile();  // Obtener el archivo XML de usuarios

    $cart = getCart();  // Obtener el carrito del usuario
    if (count($cart->item) === 0) {  // Verificar si el carrito está vacío
        echo "El carrito está vacío.";
        return;
    }


    // Variables para almacenar el total y posibles descuentos
    $total = 0;
    $discounts = 0;
    $currency = "€";  // Suponiendo que la moneda predeterminada sea euros
    $orderDetails = "";  // Para almacenar los detalles de la compra

    echo "<h2>Resumen de la compra para $username:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Producto ID</th><th>Cantidad</th><th>Precio Unitario</th><th>Total Producto</th></tr>";

    // Recorrer los productos en el carrito
    foreach ($cart->item as $item) {
        $prod_id = (string)$item->product_id;
        $quantity = (int)$item->quantity;
        $price = getItemPrice($prod_id);  // Obtener el precio del producto desde el catálogo

        if ($price === null) {
            echo "Error: Producto $prod_id no encontrado en el catálogo.<br>";
            continue;
        }

        // Calcular el total por producto
        $totalProduct = $quantity * $price;
        $total += $totalProduct;

        // Mostrar el detalle del producto
        echo "<tr>";
        echo "<td>{$prod_id}</td>";
        echo "<td>{$quantity}</td>";
        echo "<td>{$price} {$currency}</td>";
        echo "<td>{$totalProduct} {$currency}</td>";
        echo "</tr>";

        
        // Guardar detalles del pedido para el resumen
        $orderDetails .= "Producto ID: {$prod_id}, Cantidad: {$quantity}, Total: {$totalProduct} {$currency}<br>";

    }
    //verificar los descuentos

    $discountCode = $_GET['discount'];

    $discountData = getDiscount($discountCode);

    if ($discountData) {  // Si el descuento es válido (no es null)
        // Extraemos el monto del descuento y el código
        $discountCode = $discountData['code']; 
        $discountPercentage = $discountData['percentage']; 

        $discountAmount = $total * $discountPercentage / 100;  // Calcular el monto del descuento
        $total -= $discountAmount;  // Restar el descuento al total

        echo "<tr class='discount-row'><td colspan='3' align='right'>Descuentos: {$discountCode}</td><td class='discount-amount'>-{$discountAmount} {$currency}</td></tr>";
    } else {  // Si el descuento no es válido
        echo "<tr class='error-row'><td colspan='4'>Código de descuento inválido: <strong><span class='discount-code'>{$discountCode}</span></strong></td></tr>";
    }

    echo "<tr><td colspan='3' align='right'><strong>Total:</strong></td><td id='cart-total'>{$total} {$currency}</td></tr>";

    echo "</table>";

    // Verificar si el usuario tiene suficiente saldo
    foreach ($user->user as $userData) {
        if ((string)$userData->username === $username) {
            $currentBalance = (float)$userData->balance;
            
            if ($currentBalance < $total) {
                echo "<p>Error: Saldo insuficiente. Tu saldo es de {$currentBalance} {$currency}, pero necesitas {$total} {$currency}.</p>";
                return;
            }

            // Actualizar el saldo del usuario
            $userData->balance = $currentBalance - $total;
            echo "<p>Compra realizada con éxito. Nuevo saldo: " . $userData->balance . " {$currency}</p>";
            break;
        }
    }

    // Guardar la actualización del saldo en el archivo XML de usuarios
    if ($user->asXML('DB/users.xml')) {
        echo "<p>Saldo actualizado correctamente.</p>";
    } else {
        echo "<p>Error al actualizar el saldo del usuario.</p>";
    }

    // Mostrar el total de la compra
    echo "<h3>Total final de la compra: {$total} {$currency}</h3>";

    // Limpiar el carrito después de la compra
    clearCart();

    // Mostrar un resumen final con los productos comprados
    echo "<h3>Resumen de productos comprados:</h3>";
    echo $orderDetails;

    header("refresh:5;url=main.php");  // Redireccionar a la página principal después de 5 segundos
}

?>