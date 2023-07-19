<?php

namespace djinni;

class Start extends net{
    protected string $uuid;
    protected string $bufDir;
    
    protected ?string $csrToken=null;
    protected bool $userAuth = false;

    public function __construct(protected string $mail, protected string $password){
        $this->uuid = sha1( string: $this->mail.":". $this->password, binary: false);
        $this->bufDir = __DIR__."/buf";
        $this->useragent = \sys::server_userAgent();

        //Первичный конструктор на буферную директорию
        if(!file_exists(filename: $this->bufDir)) mkdir(directory: $this->bufDir, permissions: 0777);
    }
    public function __get(string $name):mixed{return $this->{$name};}
    public function __toString():string{return json_encode($this->info());}


    /** Авторизация на ресурсе */
    public function auth():bool{
        $arr_send = [
            "csrfmiddlewaretoken" => $this->get_csrToken(),
            "email" => $this->mail,
            "password" => $this->password
        ];

        //Установка заголовков
        $this->add_header("Host: djinni.co");
        $this->add_header("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8");
        $this->add_header("Content-Type: application/x-www-form-urlencoded");
        $this->add_header("Origin: https://djinni.co");
        $this->add_header("DNT: 1");
        $this->add_header("Referer: https://djinni.co/login");
        $this->add_header("Upgrade-Insecure-Requests: 1");
        $this->add_header("Sec-Fetch-Dest: document");
        $this->add_header("Sec-Fetch-Mode: navigate");
        $this->add_header("Sec-Fetch-Site: same-origin");
        $this->add_header("Sec-Fetch-User: ?1");
        $this->add_header("Pragma: no-cache");

        //Отправка запроса авторизации
        $buf = $this->send_req(url: "https://djinni.co/login", content: http_build_query(data: $arr_send));

        //Отсечение если не авторизовало
        if($buf->code !== 302) return false;

        //Отсечение второго уровня
        if($buf->redirectUrl !== "https://djinni.co/my/inbox/") return false;

        //200 not auth
        //302 auth >> redirectUrl: https://djinni.co/my/inbox/
        $this->userAuth = true;
        return true;
    }
    /** Закрыть текущую сессию */
    public function logout():void{

        //Выход
        $this->send_req(url: "https://djinni.co/logout");
        $this->userAuth = false;

        //Очистка сессии
        $this->clear_sessions();
    }
    /** Проверка на авторзацию */
    public function is_auth():bool{
        if($this->userAuth) return true;

        //Проверка авторзации загрузкой страницы сообщений
        $buf = $this->send_req("https://djinni.co/my/inbox/");
        if($buf->code === 200) return true;

        return false;
    }

    /** Загрузка писем в почтовике */
    public function load_inbox(bool $is_archive=false):array{
        $ret = [];

        $buf = $this->send_req("https://djinni.co/my/inbox/");
        if($buf->code !== 200) return $ret;

        $buf = $this->parse_inbox_msg($buf->body);

        \sys::print($buf);

        return $ret;
    }

//#############################################################################//

    /** Получение информации по классу */
    public function info():object{

        //Загрузка сессионных файлов
        $session = [];
        $session_buf  = scandir(directory: $this->bufDir);
        foreach ($session_buf as $pos=>$filename){
            if(!in_array($filename, ["", ".", "..", "..."])){
                $session[$filename] = file_get_contents($this->bufDir."/".$filename);
            }
        }

        //Формирование обьекта на выдачу
        return (object)[
            "uuid" => $this->uuid,
            "buf_dir" => $this->bufDir,
            "user"=> (object)[
                "is_auth" => $this->userAuth,
                "email" => $this->mail,
                "password" => mb_substr($this->password, 0, 4).str_repeat(".", mb_strlen($this->password)-4)
            ],
            "session" => $session
        ];
    }

    /** Очистка "сессии" */
    protected function clear_sessions():void{
        $contents = scandir(directory: $this->bufDir);
        foreach ($contents as $file) if(!in_array($file, ["", ".", "..", "..."])) unlink(filename: $this->bufDir."/".$file);
    }
    /** Получение ключа csrToken необходимого для авторизации */
    protected function get_csrToken(bool $is_load=false):string{
        $upload = $is_load;

        //Проверка на статичную отдачу
        if (!$is_load) $upload = $this->csrToken === null;

        //Отдача статики
        if(!$upload) return $this->csrToken;

        //Загрузка ключа для авторизации
        $buf = $this->send_req(url: "https://djinni.co/login");
        if($buf->code !== 200) \sys::print(code: 500, title: "Failed to load csrfMiddleWareToken");

        //Парсинг ключа
        $this->csrToken = $this->parse_csrfmiddlewaretoken(html_body: $buf->body);

        return $this->csrToken;
    }
}