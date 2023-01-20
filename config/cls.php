<?php

namespace Configurations;

class cls
{
    private $containt_tables = [];
    public $current_config;

    public function getAllFiles($folder)
    {
        $result = [];
        $tab = glob($folder . "*.{json}", GLOB_BRACE);
        foreach ($tab as $key => $value) {
            $array = explode('/', $value);
            $file = $array[count($array) - 1];
            $result[] = $file;
        }
        return $result;
    }

    public function getContaintFilesScript($configuration = [])
    {

        $this->current_config = (object)$configuration;

        $all_files = $this->getAllFiles($this->current_config->table_folder);
        $containtScripts = [];
        foreach ($all_files as $key => $table_json) {

            $table = substr($table_json, 0, strpos($table_json, '.'));

            $vars = file_get_contents($this->current_config->table_folder . $table_json);

            if (strlen($vars) > 0) {

                $script_genereted = $this->generateScript($table, $vars);

                $containtScripts[] = [
                    "table" => $table,
                    "script_create" => $script_genereted[0],
                    "script_update" => $script_genereted[1],
                ];
            }

        }

        return $containtScripts;
    }

    private function generateScript($table, $string)
    {
        $all = json_decode($string, false);

        $script = "CREATE TABLE IF NOT EXISTS " . $table . '(';
        $script_alter = "";

        foreach ($all as $key => $value) {

            $script .= $this->getString_Create_Column($key, $value);
            $db = $this->current_config->database;

            if ($this->existValue("SHOW  TABLES where Tables_in_$db='$table' ;") != null) {

                $isExist = $this->columnInTable($table, $key);

                if (!property_exists($value, 'isPrimary')) {
                    $script_alter .= $this->getString_Alter_Column($table, $key, $value->type, $isExist, $value->default);

                }


            }
        }

        $script = substr($script, 0, strlen($script) - 1) . ');';

        return [$script, $script_alter];
    }

    public function getString_Create_Column($name, $value)
    {


        $default = '';
        $isPrimary = '';

        if (property_exists($value, 'isPrimary')) {
            if ($value->isPrimary === true) {
                $isPrimary = 'PRIMARY KEY ';
            }
        }

        if (property_exists($value, 'default')) {
            if ($value->default === null || $value->default === '') {
                $default = "DEFAULT NULL";
            } else {
                $default = "DEFAULT $value->default";
            }
        }
        return $name . " $value->type  $isPrimary $default ,";
    }

    public function getString_Alter_Column($table, $name, $type, $isExist = false, $default)
    {
        if ($default === null) {
            $default = "null";
        }

        if (!$isExist) {
            return $this->getScriptColumn_create($table, $type, $name, $default);
        } else {
            return $this->getScriptColumn_alter($table, $type, $name, $default);
        }

    }

    public function getScriptColumn_alter($table, $type, $name, $default)
    {
        $type = $this->getType($type);
        return "ALTER TABLE $table CHANGE $name $name $type default $default;";
    }

    public function getScriptColumn_create($table, $type, $name, $default)
    {
        $type = $this->getType($type);
        return "ALTER TABLE $table ADD $name $type default  $default;";
    }

    public function columnInTable($table, $name)
    {
        return $this->existValue("SHOW COLUMNS FROM $table where Field='$name';");
    }

    private function getType($type)
    {
        $val = "varchar(255)";
        switch (strtoupper($type)) {
            case "STRING":
                $val = "varchar(255)";
                break;
            case "TEXT":
                $val = "text";
                break;
            case "DATE":
                $val = "date";
                break;
            case "DATETIME":
                $val = "datetime";
                break;
            case "NOMBER":
                $val = "long";
                break;
        }
        return $val;
    }
}