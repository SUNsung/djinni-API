<?php
/*
 * project: bregus.eu
 * update: 12.07.2023, 07:01
 *
 * autor: SUNsung
 * contact: git@embedded.biz.ua
 */


/** Статичный класс с разными методами */
final class sys{

    //Обработчик ошибок
    static private array $error = [];
    static public function add_error(mixed $error):void{self::$error[] = $error;}
    /** Вывод результата пользователю */
    static public function print(mixed $msg=null, string $title="OK", int $code=200):void{
        $ret_msg = (object)[];

        $ret_msg->code = $code;
        $ret_msg->msg = $title;

        //Обработка ошибок
        if(count(self::$error)>0) $ret_msg->error = self::$error;

        //Вставка тела
        if($msg !== null)$ret_msg->body = $msg;

        //Формирование ответа
        if($code < 99) $code = 100;
        if($code > 999) $code = 999;
        header("HTTP/2.0 $code");
        header("Content-Transfer-Encoding: binary");

        $file = json_encode($ret_msg, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_PRESERVE_ZERO_FRACTION);
        header("Content-Length: " . strlen($file));
        header("Content-Type: application/json; charset=UTF-8");
        print $file;

        exit();
    }

    /** Переход на другой лист с отсечением */
    static public function validateRout(string $fullpath):string{
        if(!file_exists($fullpath)) self::print(code:404, title: "Path not Found [sys]");
        return $fullpath;
    }

    //Засахарение серверных ответов
    static public function server_isSSL():bool{return $_SERVER["HTTP_SSL"]>0;}
    static public function server_contentType():string|null{return $_SERVER["HTTP_CONTENT_TYPE"];}
    static public function server_countryCode():string|null{return $_SERVER["HTTP_GEOIP_COUNTRY_CODE"];}
    static public function server_userAgent():string|null{return $_SERVER["HTTP_USER_AGENT"];}
    static public function server_ip():string|null{return $_SERVER["REMOTE_ADDR"];}
    static public function server_method():string|null{return $_SERVER["REQUEST_METHOD"];}
    static public function server_host():string|null{return $_SERVER["HTTP_HOST"];}

    /** Генерирование уникального UIID по параметрам */
    static public function generate_uiid(
        int $length=16,
        bool $numbers=true,
        bool $latin=true,
        bool $kiril=false,
        bool $upper=false,
        $hirigana=false,
        bool $katakana=false,
        bool $sumvols=false,
        bool $alt_sumvols=false
    ):string{

        $numb = "1234567890";
        $lat = "qazxswedcvfrtgbnhyujmkiolp";
        $kir = "йфячыцувсмакепитрнгоьблшщдюжзхэъїє";
        $hir = "あかさたなはまやらわがざだばぱいきしちにひみりぎじびぴうくすつぬふむゆるぐずぶぷえけせてねへめれげぜでべぺおこそとのほもよろをんごぞどぼぽ";
        $kat = "ンワラヤマハナタサカアリミヒニチシキイウクスツヌフムユルレメヘネテセケエヲロヨモホノトソコオ";
        $sum = "~!@#$%^&*()_+{}[]:<>?/.,`";
        $alt = "〄〇〓《》〒〠〰〶〷〹〸〺〻〼〽〜。〃";

        $form_krt = "";
        $uiid="";

        if($numbers)$form_krt .= $numb;
        if($latin)$form_krt .= $lat;
        if($kiril)$form_krt .= $kir;
        if($hirigana)$form_krt .= $hir;
        if($katakana)$form_krt .= $kat;
        if($sumvols)$form_krt .= $sum;
        if($alt_sumvols)$form_krt .= $alt;
        if($upper)$form_krt .= mb_strtoupper($form_krt);
        $size = mb_strlen($form_krt)-1;

        //Отсечение если строка для подбора невалидна
        if($size < 4) throw new BadFunctionCallException("Breck params in 'generate_uiid'");

        //Разбиение на массив и перемешивание в случайном порядке
        $form_krt = str_split($form_krt, 1);
        shuffle($form_krt);

        //Генерация ключа
        for($pos=0; $pos<$length; $pos++){$uiid .= $form_krt[random_int(0, $size)];}

        return $uiid;
    }
}