<?php

class Map {
    public static function generate() {
        global $config;

        $points = self::getPoints();
        $typesUsed = array();
        foreach ($points as $point) {
            $typesUsed[$point[3]] = TRUE;
        }
        $jsonPoints = json_encode($points);

        $types = $config['types'];
        foreach (array_keys($types) as $type) {
            if (!array_key_exists($type, $typesUsed)) {
                unset($types[$type]);
            }
        }
        if (!$types) {
            $types = $config['types'];
        }

        $jsonTypes = json_encode($types);
        $jsonSources = json_encode($config['sources']);

        $search = '<input type="search" id="search" placeholder="' . Translate::t('Search') . '">';

        $legendItems = array();
        foreach ($types as $key => $type) {
            $legendItems[] = '<li data-type="' . $key . '"><img src="' . $type['legendIcon'] . '" style="width: ' . $type['legendSize'][0] . 'px; height: ' . $type['legendSize'][1] . 'px;"> <span>' . htmlentities($type['label']) . ' <i>(0)</i></span></li>';
        }
        $legend = '<ul id="legend">' . implode('', $legendItems) . '</ul>';

        $credit = '';
        if (isset($config['credit']) && $config['credit']) {
            $credit = '<div id="credit">' . $config['credit'] . '</div>';
        }

        $ret = <<<HTML
<div id="map"></div>
$search
$legend
$credit
<script src="js/markerclusterer.js"></script>
<script src="js/jquery.friendly_id.js"></script>
<script src="js/script.js"></script>
<script>
    var Map = {
        types: $jsonTypes,
        sources: $jsonSources,
    };
    
    function initMap() {
        Map.map = new google.maps.Map(document.getElementById('map'), {
            zoom: {$config['map']['zoom']},
            center: {
                lat: {$config['map']['center']['latitude']},
                lng: {$config['map']['center']['longitude']}
            }
        });
        
        var search = document.getElementById('search');
        search.onchange = searchChanged;
        search.onkeyup = searchChanged;
        search.onsearch = searchChanged;
        Map.map.controls[google.maps.ControlPosition.RIGHT_TOP].push(search);
        
        var legend = document.getElementById('legend');
        legend.onclick = toggleLegend;
        Map.map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
        
        var credit = document.getElementById('credit');
        if (credit) {
            Map.map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(credit);
        }
        
        Map.infoWindow = new google.maps.InfoWindow({
            maxWidth: 400
        });
        
        var points = $jsonPoints;
        window.allMarkers = points.map(processPoint);
        window.markerClusterer = new MarkerClusterer(Map.map, window.allMarkers, {imagePath: 'img/clusters/m', maxZoom: 17});
        updateLegendCounts();
    }
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key={$config['map']['apiKey']}&language={$config['map']['language']}&region={$config['map']['region']}&callback=initMap"></script>
HTML;

        return $ret;
    }

    public static function getPoints() {
        global $config;

        $points = array();

        foreach (array_keys($config['sources']) as $source) {
            $points = array_merge($points, Spreadsheet::read($source));
        }

        self::dedupePoints($points);

        return $points;
    }

    /**
     * Makes sure no two points are too close (or exactly the same). Moves them
     * by a bit if necessary.
     *
     * @param array $points
     */
    private static function dedupePoints(&$points) {
        $positions = array();

        foreach ($points as &$point) {
            $lat = round($point[1], 5);
            $long = round($point[2], 5);

            $distance = 1; $direction = array(0, 0); $direction_index = 0;
            while (isset($positions['p' . ($lat + $distance * $direction[0] * .00001)]['p' . ($long + $distance * $direction[1] * .00001)])) {
                $direction_index++;
                if ($direction_index == 9) {
                    $distance++;
                    $direction_index = 1;
                }
                switch ($direction_index) {
                    case 0: $direction = array(0, 0); break;
                    case 1: $direction = array(0, 1); break;
                    case 2: $direction = array(1, 0); break;
                    case 3: $direction = array(0, -1); break;
                    case 4: $direction = array(-1, 0); break;
                    case 5: $direction = array(1, 1); break;
                    case 6: $direction = array(-1, 1); break;
                    case 7: $direction = array(-1, -1); break;
                    case 8: $direction = array(1, -1); break;
                }
            }

            if ($direction_index > 0) {
                $point[1] += ($distance * $direction[0] * .00003);
                $point[2] += ($distance * $direction[1] * .00003);
            }

            $lat = round($point[1], 5);
            $long = round($point[2], 5);
            if (!isset($positions['p' . $lat])) {
                $positions['p' . $lat] = array();
            }
            $positions['p' . $lat]['p' . $long] = TRUE;
        }
    }
}
