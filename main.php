<head>
    <title>Carrito</title>
    <link rel="stylesheet" type="text/css" href="css/global.css">
</head>

<?php
// include_once 'components/cart/cart.php';
// include_once 'components/user/user.php';
// include_once 'components/user/login.php';
// include_once 'components/checkout/checkout.php';
// include_once 'components/catalog/catalog.php';


// innitSession();
// // addToCart(2,30,100,'EUR');
// // removeProduct(3,1);
// // addUser('carlos','carlos*','pepe@email.com');
// // removeUser('carlos');
// // getUserID('carlos');
// // login();
// // removeFromCart(2,5);
// // viewCart();
// // getUserBalance('carlos');
// // viewCatalog();
// // addBalance('carlos', 100);
// logout();
session_start();

include 'components/user/user.php';  // Archivo que maneja login, sesión
include 'components/cart/cart.php';  // Archivo que maneja carrito

// Verificar si hay sesión iniciada
if (!isset($_SESSION['username'])) {
    if (isset($_GET['username']) && isset($_GET['password'])) {
        login($_GET['username'], $_GET['password']);  // Llamar a la función de login
    } else {
        // Mostrar mensaje si no ha iniciado sesión y no se envían credenciales
        echo "Debe iniciar sesión para continuar.<br>";
        echo "Ingrese sus credenciales en la barra de búsqueda: ?username=pepe&password=pepe";
        return;
    }
} 

// Si el usuario ya ha iniciado sesión, mostrar las opciones
if (isset($_SESSION['username'])) {
    echo "<h2>Bienvenido, " . $_SESSION['username'] . "</h2>";

    // Procesar acciones según los parámetros de la URL
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        switch ($action) {
            case 'view_cart':
                viewCart();  // Mostrar el contenido del carrito
                break;

            case 'add_to_cart':
                if (isset($_GET['prod_id'], $_GET['quantity'], $_GET['price'], $_GET['currency'])) {
                    addToCart($_GET['prod_id'], $_GET['quantity'], $_GET['price'], $_GET['currency']);
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

            case 'clear_cart':
                clearCart();  // Limpiar el carrito
                break;

            case 'view_catalog':
                viewCatalog();  // Mostrar el catálogo
                break;

            case 'add_balance':
                if (isset($_GET['username'], $_GET['amount'])) {
                    addBalance($_GET['username'], $_GET['amount']);
                } else {
                    echo "Faltan parámetros para añadir saldo.";
                }
                break;
            
            case 'logout':
                logout();  // Cerrar sesión
                break;
                

            case 'checkout':
                checkout();  // Procesar el pago (función que debes definir)
                break;

            default:
                echo "Acción no reconocida.";
        }
    } else {
        // Si no hay acción específica, mostrar el catálogo por defecto
        showCatalog();
    }
}

// Función que muestra el catálogo
function showCatalog() {
    $catalog = [
        ['id' => 1, 'name' => 'Producto 1', 'price' => 10],
        ['id' => 2, 'name' => 'Producto 2', 'price' => 20],
        ['id' => 3, 'name' => 'Producto 3', 'price' => 30],
    ];

    echo "<h2>Catálogo de Productos</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th></tr>";

    foreach ($catalog as $product) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>{$product['price']}</td>";
        echo "</tr>";
    }

    echo "</table>";
    echo "<br>Para añadir productos al carrito use la barra de búsqueda:<br>";
    echo "?action=add_to_cart&prod_id=1&quantity=2&price=10&currency=EUR<br><br>";
}
?>

