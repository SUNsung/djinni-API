<?php

namespace djinni;

class net extends parse{
    protected string $useragent;
    protected array $headers;

    /** Встановлення юзерагента */
    public function set_useragent(string $useragent):void{$this->useragent = $useragent;}

    /** Добавання заголовку */
    public function add_header(string $headers):void{if(mb_strlen($headers) > 3) $this->headers[] = $headers;}

    /** Очистка заголовків */
    public function clear_headers():void{ $this->headers = [];}


    /** Відправка запроса на сервер */
    public function send_req(string $url, string $content=""):reqObj{
        $cookiePath = $this->bufDir.'/cookie.mxt';

        $this->headers[] = 'User-Agent: '.$this->useragent;
        $this->headers[] = 'Accept-Language: ru,en;q=0.5';


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiePath);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiePath);

        //Обработка POST
        if($content !== ""){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            curl_setopt($ch, CURLOPT_POST, true);

        }else{//Обработка GET
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        }

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOBODY, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);


        $this->headers = [];
        $return = new reqObj();

        $return->code = $info["http_code"];
        $return->scheme = $info["scheme"];
        $return->contentType = $info["content_type"];
        $return->ip = $info["primary_ip"];
        $return->port = $info["primary_port"];

        $return->url = $url;
        $return->redirectUrl = $info["redirect_url"];

        $return->body = $result;

        return $return;
    }
}