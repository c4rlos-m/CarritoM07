<head>
    <title>Carrito</title>
    <link rel="stylesheet" type="text/css" href="css/global.css">
    
</head>

<?php
session_start();

include 'com/user/user.php';  
include 'com/cart/cart.php';  
include 'com/catalog/catalog.php';  
include 'com/checkout/checkout.php';  


if (!isset($_SESSION['username'])) {
    if (isset($_GET['username']) && isset($_GET['password'])) {
        login($_GET['username'], $_GET['password']);  
    } else {
        echo "Debe iniciar sesión para continuar.<br>";
        echo "Ingrese sus credenciales en la barra de búsqueda: ?username=pepe&password=pepe";
        return;
    }
} 

if (isset($_SESSION['username'])) {
    echo '<div class="header-container">';
    echo '<h2 class="welcome-message">Bienvenido, ' . $_SESSION['username'] . '</h2>';
    echo '<h2 class="balance-info" id="user-balance">Saldo en la cuenta: ' . getUserBalance($_SESSION['username']) . ' EUR</h2>';
    echo '</div><br>';


    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        switch ($action) {

            case 'view_catalog':
                viewCatalog();  
                break;

            case 'view_cart':
                viewCart();  
                break;

            case 'add_to_cart':
                if (isset($_GET['prod_id'], $_GET['quantity'])) {
                    
                    addToCart($_GET['prod_id'], $_GET['quantity']);
                } else {
                    echo "Faltan parámetros para añadir al carrito.";
                }
                break;
            
            case 'remove_from_cart':
                if (isset($_GET['prod_id'], $_GET['quantity'])) {
                    removeFromCart($_GET['prod_id'], $_GET['quantity']);
                } else {
                    echo "Faltan parámetros para eliminar del carrito.";
                }
                break;
            case 'update_cart':
                if (isset($_GET['prod_id'], $_GET['quantity'])) {
                    updateCart($_GET['prod_id'], $_GET['quantity']);
                } else {
                    echo "Faltan parámetros para actualizar el carrito.";
                }
                break;

            case 'empty_cart':
                emptyCart();  
                break;

            
            case 'add_balance':
                if (isset($_GET['amount'])) {
                    addBalance($_GET['amount']);
                } else {
                    echo "Faltan parámetros para añadir saldo.";
                }
                break;
            
            case 'logout':
                logout();  
                break;
                

            case 'checkout':
                checkout();  
                break;

            default:
                echo "Acción no reconocida.";
        }
    } else {
        
        viewCatalog();

    }
}


?>

