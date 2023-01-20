<?php

namespace Configurations;

use DateTime;
use PDO;

class vars extends cls
{
    private $configurations = null;
    private $tables = [];
    private $scripts = [];
    private $database = [];
    private $orm = [];
    public $dbo = null;

    public function __construct($autoCreate = false)
    {
        $this->setConfigurations((object)json_decode(file_get_contents('./config/init.json'), true));
        $this->setOrm((object)$this->getConfigurations()->orm);
        $this->setDatabase((object)$this->getConfigurations()->database);
        if ($autoCreate) {
            $this->AutoCreateDataBase();
        }
    }

    /**
     * @return null
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @param null $configurations
     * @return vars
     */
    public function setConfigurations($configurations)
    {
        $this->configurations = $configurations;
        return $this;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param array $tables
     * @return vars
     */
    public function setTables($tables)
    {
        $this->tables = $tables;
        return $this;
    }

    /**
     * @return null
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * @param null $scripts
     * @return vars
     */
    public function setScripts($scripts)
    {
        $this->scripts = $scripts;
        return $this;
    }

    /**
     * @return object
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param object $database
     * @return vars
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @return Object
     */
    public function getOrm()
    {
        return $this->orm;
    }

    /**
     * @param Object $orm
     * @return vars
     */
    public function setOrm($orm)
    {
        $this->orm = $orm;
        return $this;
    }

    public function con()
    {
        $dbo = null;
        try {
            $timezone_config = "Africa/Cairo";

            date_default_timezone_set($timezone_config);

            $now = new DateTime();
            $mins = $now->getOffset() / 60;
            $sgn = ($mins < 0 ? -1 : 1);
            $mins = abs($mins);
            $hrs = floor($mins / 60);
            $mins -= $hrs * 60;
            $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $dbo = new PDO("mysql:host=" . $this->getDatabase()->host . ";dbname=" . $this->getDatabase()->name . "", $this->getDatabase()->username, $this->getDatabase()->password, $pdo_options);
            $dbo->exec("SET time_zone='$offset';");
            $dbo->query('SET NAMES UTF8');
        } catch (Exception $exception) {
            var_dump($exception);
//            $this->setError('CONNECTION_BASE_DES_DONNEES', $exception);
        }
        return $dbo;
    }

    public function getValue($rqt, $data = [])
    {
        $var = null;
        try {
            $req = $this->con()->prepare($rqt);
            $req->execute($data);
            while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
                $var = $data['i'];
            }
        } catch (Exception $exception) {
            $this->isError($exception, $rqt);
        }
        return $var;
    }

    public function getDbo()
    {
        if ($this->dbo == null) {
            $this->con();
        }

        return $this->dbo;
    }

    public function existValue($rqt, $data = [])
    {
        try {
            $req = $this->con()->prepare($rqt);
            $req->execute($data);
            while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
                return true;
            }
        } catch (Exception $exception) {
            $this->setError(201, "Une erreur s'est produite", $exception);
        }

        return false;
    }


    public function execute($rqt, $data = [], $message_success = "Traitement réussie avec success", $message_erreur = "Une erreur s'est produite")
    {
        $bool = false;
        try {
            $req = $this->con()->prepare($rqt);
            $bool = $req->execute($data);
            $this->setResponse($message_success, 200);
            return $bool;
        } catch (Exception $th) {
            var_dump($th);
        }

        return false;
    }

    public $error = ["code" => 200, "message" => "success", "log" => null, "querry" => null];

    public function setError($code, String $message, $log = null, $querry = null)
    {
        $this->error["code"] = $code;
        $this->error["message"] = $message;
        $this->error["log"] = $log;
        $this->error["querry"] = $querry;
    }

    public $data_response = ["message" => "success", "status" => 200, "data" => null];

    public function setResponse($message, $status = 200, $data = [], $token = null)
    {
        $default_folde = '/uploads/' . $this->getDatabase()->name . '/';
        $data = ($data == []) ? $this->error : $data;
        if ($token == null) {

            $this->data_response = ["message" => $message, "status" => $status, "data" => $data, 'file_folder' => $default_folde];
        } else {
            $this->data_response = ["message" => $message, "status" => $status, "data" => $data, 'token' => $token, 'file_folder' => $default_folde];
        }
    }

    public function AutoCreateDataBase()
    {
        $this->scripts = $this->getContaintFilesScript(
            [
                "table_folder" => $this->getOrm()->folder_table,
                "database" => $this->getDatabase()->name
            ]
        );

        foreach ($this->scripts as $key => $value) {
            $table = $value["table"];
            $db = $this->getDatabase()->name;

            if ($this->existValue("SHOW  TABLES where Tables_in_$db='$table' ;") == null) {
                // Si la table n 'existe, nous la créons
                $this->execute($value["script_create"]);
            } else {
                // Si la table existe, nous modifions les attributs
                if (strlen($value["script_update"]) > 1) {
                    $this->execute($value["script_update"]);
                }
            }
        }
    }

}