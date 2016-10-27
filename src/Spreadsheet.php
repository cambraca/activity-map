<?php

class Spreadsheet {
    const BASE_URL = 'https://spreadsheets.google.com/feeds/list/';

    private $data = NULL;
    private $source = NULL;
    private $fields = NULL;


    public function __construct($source) {
        global $config;

        $this->source = $source;

        $id = $config['sources'][$source]['spreadsheetId'];

        $url = self::BASE_URL . $id . '/2/public/values?alt=json';
        $json_data = file_get_contents($url);
        if (!$json_data) {
            return;
        }

        $data = json_decode($json_data, TRUE);
        if (!isset($data['feed']['entry'])) {
            return;
        }
        $this->fields = $this->parseFields($data);

        $url = self::BASE_URL . $id . '/1/public/values?alt=json';
        $json_data = file_get_contents($url);
        if (!$json_data) {
            return;
        }

        $data = json_decode($json_data, TRUE);
        if (!isset($data['feed']['entry'])) {
            return;
        }

        $this->data = $data;
    }

    private function parseFields($data) {
        $fields = array();
        foreach ($data['feed']['entry'] as $entry) {
            if (isset($entry['gsx$field']['$t'])) {
                $fields[$this->toHeader($entry['gsx$field']['$t'])] = $entry['gsx$field']['$t'];
            }
        }
        return $fields;
    }

    private function toHeader($string) {
        return preg_replace_callback('/[^a-z]+/', function ($matches) {
            return strtoupper($matches[0]);
        }, strtolower(str_replace(' ', '', $string)));
    }

    private function getPoints() {
        $points = array();


        foreach ($this->data['feed']['entry'] as $entry) {
            $point = $this->getSinglePoint($entry);

            if ($point) {
                $points[] = $point;
            }
        }

        return $points;
    }

    private function getSinglePoint($row) {
        global $config;

        $main_fields = array('latitude', 'longitude', 'type', 'date');
        $types_mapping = array();
        foreach ($config['types'] as $key => $value) {
            $types_mapping[$value['label']] = $key;
        }

        $point = array($this->source);

        foreach ($main_fields as $field) {
            switch ($field) {
                case 'type':
                    if (!array_key_exists($row['gsx$' . $field]['$t'], $types_mapping)) {
                        return NULL;
                    }

                    $point[] = $types_mapping[$row['gsx$' . $field]['$t']];
                    break;
                case 'latitude':
                case 'longitude':
                    $point[] = doubleval($row['gsx$' . $field]['$t']);
                    break;
                default:
                    $point[] = $row['gsx$' . $field]['$t'];
            }
        }

        $extra_data = array();
        foreach ($row as $key => $value) {
            if (!$value['$t']) {
                continue;
            }

            if (substr($key, 0, 4) !== 'gsx$') {
                continue;
            }

            $stripped_key = substr($key, 4);

            if (in_array($stripped_key, $main_fields)) {
                continue;
            }

            if (!array_key_exists($stripped_key, $this->fields)) {
                continue;
            }

            $extra_data[$this->fields[$stripped_key]] = $value['$t'];
        }
        $point[] = $extra_data;

        return $point;
    }

    public static function read($source) {
        $s = new Spreadsheet($source);
        return $s->getPoints();
    }
}
