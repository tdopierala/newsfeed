<?php

    $html = file_get_contents("http://www.ftb.pl/aktualnosci_artykuly_7.htm");

    //echo $html;

    $DOM = new DOMDocument;
    $DOM->loadHTML($html);

    //get all H1
    $items = $DOM->getElementsByTagName('li');

    for ($i = 0; $i < $items->length; $i++){
        print_r($items->item($i));
        
        echo "\n---\n";
    }

    //print_r($items);