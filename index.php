<?php


use Configurations\vars;
include './config/cls.php';
include './config/vars.php';
include './config/dbo.php';

$vars = new vars(true);
var_dump($vars->data_response);

