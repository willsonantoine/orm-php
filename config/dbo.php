<?php

namespace Configurations;

class dbo extends vars
{
    static $timezone_config;
    public $dbo =null;

    public function columnInTable($table, $name)
    {
        return $this->existValue("SHOW COLUMNS FROM $table where Field='$name';");
    }

    public function existValue($rqt, $data = [])
    {
        try {
            $req = $this->getDbo()->prepare($rqt);
            $req->execute($data);
            while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
                return true;
            }
        } catch (Exception $exception) {
           var_dump($exception);
        }

        return false;
    }


}