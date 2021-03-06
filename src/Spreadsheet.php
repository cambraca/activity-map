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
            if (isset($entry['gsx$' . $this->toHeader(Translate::t('Field'))]['$t'])) {
                $fields[$this->toHeader($entry['gsx$' . $this->toHeader(Translate::t('Field'))]['$t'])] = $entry['gsx$' . $this->toHeader(Translate::t('Field'))]['$t'];
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

        $main_fields = array('Latitude', 'Longitude', 'Type', 'Date');
        $main_fields = array_map(array('Translate', 't'), $main_fields);
        $main_fields = array_map(array($this, 'toHeader'), $main_fields);
        $types_mapping = array();
        foreach ($config['types'] as $key => $value) {
            $types_mapping[$value['label']] = $key;
        }

        $point = array($this->source);

        foreach ($main_fields as $field) {
            switch ($field) {
                case $this->toHeader(Translate::t('Type')):
                    if (!array_key_exists($row['gsx$' . $field]['$t'], $types_mapping)) {
                        return NULL;
                    }

                    $point[] = $types_mapping[$row['gsx$' . $field]['$t']];
                    break;
                case $this->toHeader(Translate::t('Latitude')):
                case $this->toHeader(Translate::t('Longitude')):
                    $value = $row['gsx$' . $field]['$t'];
                    if ($value === '') {
                        return NULL;
                    }
                    $point[] = doubleval($value);
                    break;
                case $this->toHeader(Translate::t('Date')):
                    $date = $row['gsx$' . $field]['$t'];
                    $endDateKey = 'gsx$' . $this->toHeader(Translate::t('End date'));
                    if (isset($row[$endDateKey]['$t']) && $row[$endDateKey]['$t'])
                        $date .= html_entity_decode(' &ndash; ') . $row[$endDateKey]['$t'];
                    $point[] = $date;
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
