<?php


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

    //Засахарение серверных ответов
    static public function server_isSSL():bool{return $_SERVER["HTTP_SSL"]>0;}
    static public function server_contentType():string|null{return $_SERVER["HTTP_CONTENT_TYPE"];}
    static public function server_countryCode():string|null{return $_SERVER["HTTP_GEOIP_COUNTRY_CODE"];}
    static public function server_userAgent():string|null{return $_SERVER["HTTP_USER_AGENT"];}
    static public function server_ip():string|null{return $_SERVER["REMOTE_ADDR"];}
    static public function server_method():string|null{return $_SERVER["REQUEST_METHOD"];}
    static public function server_host():string|null{return $_SERVER["HTTP_HOST"];}
}
