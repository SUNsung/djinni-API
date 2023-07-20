<?php

//Первичный автозагрущик
require_once __DIR__."/bin/autoload.php";

/** @var \djinni\Start $DJ Обьект класса работы с djinni */
$DJ = new \djinni\Start(mail: sysConstants::$user_email, password: sysConstants::$user_pass);

//Выход из сессии
//$DJ->logout();

//Проверка авторизации и авторизация если нужно
if(!$DJ->is_auth()){
    $rez = $DJ->auth();
    if (!$rez) sys::print(code: 401, title: "Unauthorized");
}

sys::print($DJ->load_profileView(), "head");

$arr = [
    "profileView" => $DJ->load_profileView(), //Список пользователей что просматривали профиль за последний месяц
    "jobsFilter" => $DJ->load_jobsFilter(),   //Обьект указателей на поиск
    "archive" => $DJ->load_inbox(is_archive: true),  //получение сообщений из архива
    "inbox" => $DJ->load_inbox()   //Получение сообщений из почтового ящика
];
sys::print($arr);

//Финальная заглушка
sys::print(code: 400, title: "Bad Request [global]", msg: "Rout not catch");
