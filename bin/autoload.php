<?php

/**
 * Первичная загрузка всего нужного
 * Автозагрущик внутренних дерикторий
 */

//проверка на версию php
if(floatval(mb_substr(phpversion(), 0, 3)) < 8.2){
    echo json_encode(["code" => 500, "msg" => "Invalid php version [".phpversion()."]. Requires 8.2 or higher."]);
    exit();
}

// Уставновка нулевого часового пояса
date_default_timezone_set('Etc/GMT+0');

//Подключение обязательных классов
require_once __DIR__."/.conf.php";
require_once __DIR__."/sys.php";

//Автозагрущик классов
spl_autoload_register(function (string $class_name):void{
    $class_name = str_replace('\\', '/', $class_name);
    require_once __DIR__."/".$class_name.".php";
});

