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
    
}


?>