<?php

/**
 * Copy this file as `config.php`, and modify to configure the map.
 */

$config = array();

$config['title'] = 'My map';

/**
 * Credit, shown in the bottom left (may contain HTML).
 */
$config['credit'] = '';

$config['map'] = array(
    'apiKey' => '', // Replace with your Google API key
    'zoom' => 8,
    'center' => array(
        'latitude' => 0,
        'longitude' => 0,
    ),
    'language' => 'en',
    'region' => 'US',
);

$config['translations'] = array(
    'Search' => 'Buscar',
    'Latitude' => 'Latitud',
    'Longitude' => 'Longitud',
    'Type' => 'Tipo',
    'Date' => 'Fecha',
);

$config['types'] = array(
    'type_a' => array(
        'label' => 'Type A',
        'icon' => 'img/types/a.png',
        'size' => array(22, 40),
        'origin' => array(0, 0),
        'anchor' => array(11, 40),
        'legendIcon' => 'img/types/a_square.png',
        'legendSize' => array(22, 22),
    ),
);

$config['sources'] = array(
    'sheet_a' => array(
        'spreadsheetId' => '', // Replace with Google Spreadsheet ID
        'label' => 'Sheet A',
        'logo' => 'img/sources/a.png',
    ),
);
