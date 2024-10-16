<?php
function getDiscountsFile(){
    $discountsFile = 'DB/validDiscounts.xml'; 

    return simplexml_load_file($discountsFile);
}

function getDiscount($discountCode) {
    $discounts = getDiscountsFile(); 

    foreach ($discounts->discount as $discount) {
        if ((string)$discount->code === (string)$discountCode) {
            return [
                'code' => (string)$discount->code, 
                'percentage' => (float)$discount->percentage 
            ];
        }
    }
    return null; 
}


function verifyDiscount($discountCode) {
    $discounts = getDiscountsFile(); 

    foreach ($discounts->discount as $discount) {
        if ((string)$discount->code === (string)$discountCode) {
            return true; 
        }
    }
    return false; 
}

?>