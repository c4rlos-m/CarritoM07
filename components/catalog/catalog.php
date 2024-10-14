<?php

function getCatalogFile(){
    $catalogFile = 'DB/catalog.xml'; 

    if (!file_exists($catalogFile)) {
        $catalog = new SimpleXMLElement('<products></products>');
        $catalog->asXML($catalogFile); 
    } else {
        $catalog = simplexml_load_file($catalogFile);
    }

    return $catalog;
}

function getItemPrice($prod_id) {
    $catalog = getCatalogFile(); 

    foreach ($catalog->product as $item) {
        if ((string)$item->id === (string)$prod_id) {
            return (float)$item->price; 
        }
    }
    return null; 
}

function getItemStock($prod_id) {
    $catalog = getCatalogFile(); 

    foreach ($catalog->product as $item) {
        if ((string)$item->id === (string)$prod_id) {
            return (int)$item->stock; 
        }
    }
    return null; 
}

function addStock($prod_id, $quantity){
    $catalog = getCatalogFile();

    foreach ($catalog->item as $item) {
        if ((string)$item->id === (string)$prod_id) {
            $item->stock = (int)$item->stock + $quantity;
            echo "Stock actualizado correctamente";
            break;
        }
    }

    if($catalog->asXML('DB/catalog.xml')){
        echo "Stock actualizado correctamente <br>";
    }
}

function addProducttoCatalog($prod_id, $name, $price, $stock){
    $catalog = getCatalogFile();

    $newProduct = $catalog->addChild('product');
    $newProduct->addChild('id', $prod_id);
    $newProduct->addChild('name', $name);
    $newProduct->addChild('price', $price);
    $newProduct->addChild('stock', $stock);

    if($catalog->asXML('DB/catalog.xml')){
        echo "Producto añadido correctamente <br>";
    }
}

function removeStock($prod_id, $quantity){
    $catalog = getCatalogFile();

    foreach ($catalog->product as $item) {
        if ((string)$item->id === (string)$prod_id) {
            $currentStock = (int)$item->stock;
            if ($currentStock >= $quantity) {
                $item->stock = $currentStock - $quantity; 
                echo "Stock actualizado correctamente para el producto ID $prod_id.<br>";
                break;
            } else {
                echo "Error: No hay suficiente stock para reducir.<br>";
                return; 
            }
        }
    }

    
    if ($catalog->asXML('DB/catalog.xml')) {
        echo "Stock guardado correctamente en el catálogo.<br>";
    } else {
        echo "Error al guardar el stock en el catálogo.<br>";
    }
}


function viewCatalog(){
    $catalog = getCatalogFile();

    echo "<h2>Catalogo de Venta:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Producto ID</th><th>Nombre</th><th>Precio (€)</th><th>Stock</th></tr>";
    
    foreach ($catalog->product as $item) {
        echo "<tr>";
        echo "<td>" . (string)$item->id . "</td>";
        echo "<td>" . (string)$item->name . "</td>";
        echo "<td>" . (string)$item->price . "</td>";
        echo "<td>" . (string)$item->stock . "</td>";
        echo "</tr>";


    }
    echo "</table>";
    echo "<br>Para añadir productos al carrito use la barra de búsqueda:<br>";
    echo "?action=add_to_cart&amp;prod_id=1&amp;quantity=2<br>";
    
}


?>