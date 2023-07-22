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


$DJ->start_search(all_page: true)
    ->add_specialization("PHP")
    ->add_english("no_english")->add_english("basic")->add_english("pre")
    ->add_salaryFrom("1500")
    ->add_employment("remote");

$search = $DJ->load_jobsBySearch();

sys::print($search, "Search");


$DJ->start_search(pages: 2)->add_specialization("PHP"); //Установка параметров для поиска вакансий
$arr = [
    "profileView" => $DJ->load_profileView(),           //Список пользователей что просматривали профиль за последний месяц
    "jobsFilter" => $DJ->load_jobsFilter(),             //Обьект указателей на поиск
    "search" => $DJ->load_search(),                     //Получение вакансий по поисковым параметрам
    "archive" => $DJ->load_inbox(is_archive: true),     //получение сообщений из архива
    "inbox" => $DJ->load_inbox()                        //Получение сообщений из почтового ящика
];
sys::print($arr);

//Финальная заглушка
sys::print(code: 400, title: "Bad Request [global]", msg: "Rout not catch");
