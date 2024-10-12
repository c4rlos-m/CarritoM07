<?php
function getDiscountsFile(){
    $discountsFile = 'DB/validDiscounts.xml'; 

    return simplexml_load_file($discountsFile);
}

function getDiscount($discountCode) {
    $discounts = getDiscountsFile(); // Obtener el archivo de descuentos

    foreach ($discounts->discount as $discount) {
        if ((string)$discount->code === (string)$discountCode) {
            return [
                'code' => (string)$discount->code, // Retornar el código de descuento
                'percentage' => (float)$discount->percentage // Retornar el monto del descuento
            ];
        }
    }
    return null; // Retornar null si no se encuentra el descuento
}


function verifyDiscount($discountCode) {
    $discounts = getDiscountsFile(); // Obtener el archivo de descuentos

    foreach ($discounts->discount as $discount) {
        if ((string)$discount->code === (string)$discountCode) {
            return true; // Retornar true si el descuento es válido
        }
    }
    return false; // Retornar false si el descuento no es válido
}

?>