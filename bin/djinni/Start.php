<?php

namespace djinni;

require_once "reqObj.php";
require_once "filterObj.php";
require_once "inboxMsgObj.php";
require_once "searchObj.php";
require_once "session.php";
require_once "search.php";
require_once "parse.php";
require_once "net.php";

class Start extends net{
    public const ver = "1.0.0";
    protected string $uuid;
    protected string $bufDir;
    
    protected ?string $csrToken=null;
    protected bool $userAuth = false;
    protected const session_uuid="djinni_class_";

    private ?search $search = null;
    private bool $search_allPage;
    private int $search_pages;

    public function __construct( #[\SensitiveParameter] protected string $mail="", protected string $password=""){
        session_start();

        $this->uuid = sha1( string: $this->mail.":". $this->password, binary: false);
        $this->bufDir = __DIR__."/buf";
        $this->useragent = is_string($_SERVER["HTTP_USER_AGENT"])? $_SERVER["HTTP_USER_AGENT"]:"Djinni API";

        //Первичный конструктор на буферную директорию
        if(!file_exists(filename: $this->bufDir)) mkdir(directory: $this->bufDir, permissions: 0777);
    }
    public function __get(string $name):mixed{return $this->{$name};}
    public function __toString():string{return json_encode($this->info());}

//⬇⬇############################⬇⬇[ AUTH ONLY ]⬇⬇############################⬇⬇//
    /** Очистка "сессії" */
    protected function clear_sessions():void{
        $contents = scandir(directory: $this->bufDir);
        foreach ($contents as $file) if(!in_array($file, ["", ".", "..", "..."])) unlink(filename: $this->bufDir."/".$file);
    }
    /** Отримання ключа csrToken необхідного для авторизації */
    protected function get_csrToken(bool $is_load=false):string{
        $upload = $is_load;

        //Проверка на статичную отдачу
        if (!$is_load) $upload = $this->csrToken === null;

        //Отдача статики
        if(!$upload) return $this->csrToken;

        //Загрузка ключа для авторизации
        $buf = $this->send_req(url: "https://djinni.co/login");
        if($buf->code !== 200) throw new \ErrorException("Failed to load csrfMiddleWareToken");

        //Парсинг ключа
        $this->csrToken = $this->parse_csrfmiddlewaretoken(html_body: $buf->body);

        return $this->csrToken;
    }

    /** Авторизація на ресурсі */
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
        $this->uptime_auth();
        return true;
    }
    /** Закрити сессію */
    public function logout():void{

        //Выход
        $this->send_req(url: "https://djinni.co/logout");
        $this->userAuth = false;

        $this->clear_auth();

        //Очистка сессии
        $this->clear_sessions();
    }
    /** Перевірка на авторзацію */
    public function is_auth():bool{
        if($this->userAuth) return true;

        //ПРоверка по времени последней проверки
        if($this->is_existsAuth()){
            if($this->time_auth()+60*15 > strtotime("now")) return true;
        }

        //Проверка авторзации загрузкой страницы сообщений
        $buf = $this->send_req("https://djinni.co/my/inbox/");
        if($buf->code === 200){
            $this->uptime_auth();
            return true;
        }

        return false;
    }
    private function is_existsAuth():bool{return isset($_SESSION[$this::session_uuid."auth_time"]);}
    private function time_auth():int{return intval($_SESSION[$this::session_uuid."auth_time"]);}
    private function uptime_auth():void{if($this->userAuth) $_SESSION[$this::session_uuid."auth_time"] = strtotime("now");}
    private function clear_auth():void{unset($_SESSION[$this::session_uuid."auth_time"]);}

    /** Завантаження листів */
    public function load_inbox(bool $is_archive=false):array{
        $ret = [];

        //получение страницы
        $buf = $this->send_req("https://djinni.co/my/inbox/".($is_archive? "archive/":""));
        if($buf->code !== 200) return $ret;
        $this->uptime_auth();

        //Парсинг контента из страницы и отдача
        return $this->parse_inbox_msg($buf->body);
    }

    /** Отримання списку тіх хто дивився профіль за останні 30 днів */
    public function load_profileView():array{
        $ret = [];

        //получение страницы
        $buf = $this->send_req("https://djinni.co/my/dashboard/");
        if($buf->code !== 200) return $ret;
        $this->uptime_auth();

        //Парсинг контента из страницы и отдача
        return $this->parse_profileView($buf->body);
    }
//⬆⬆############################⬆⬆[ AUTH ONLY ]⬆⬆############################⬆⬆//

    /** Отримання дерева пошукових параметрів */
    public function load_jobsFilter():filterObj|bool{

        //получение страницы
        $buf = $this->send_req("https://djinni.co/jobs/");
        if($buf->code !== 200) return false;

        //Парсинг контента из страницы и отдача
        return $this->parse_jobs_filter($buf->body);
    }

    /** Отримання результу пошуку */
    public function load_jobsBySearch(bool|null $all_page=null, int|null $pages=null):array{
        if($this->search === null) return [];

        $ret = [];
        if($pages !== null) $this->search_pages = $pages;
        if($all_page !== null) $this->search_allPage = $all_page;
        if($this->search_pages < 1) $this->search_pages = 1;


        //получение страницы
        $first_page = $this->send_req($this->search->get_url());
        if($first_page->code !== 200) return $ret;
        $this->uptime_auth();

        //Обработка загрузки нескольких страниц
        if($this->search_pages > 1 || $this->search_allPage){


            //Парсинг контента из страницы
            $parse_arr = $this->parse_search_content($first_page->body);

            //Получение только больших от исходной страниц
            $page = $this->search->get_page(); $pages = [];
            foreach ($this->parse_search_pages($first_page->body) as $num) if($page<$num) $pages[] = $num;

            //Получение пределов сколько страниц загружать
            $max_page = end($pages);
            if(!$this->search_allPage){
                if($this->search_pages > 1){
                    $buf_page = $page+$this->search_pages-1;
                    if($buf_page < $max_page) $max_page = $buf_page;
                }
            }

            //Перебор периода страниц
            for($pp=$page+1;$pp<=$max_page;$pp++){
                $this->search->page($pp);
                $content = $this->send_req($this->search->get_url());

                //Парсинг только спешных
                if($content->code === 200){

                    //Слияние с уже загруженым
                    $parse_arr = array_merge($parse_arr, $this->parse_search_content($content->body));
                }
            }

            //Возврат польного массива
            return $parse_arr;

            //ВОзврат массива ондой страницы
        }else return $this->parse_search_content($first_page->body);
    }

    /** Запуск конструктора пошукового запросу */
    public function start_search(bool $all_page=false, int $pages=1):search{
        $this->search = new \djinni\search();
        $this->search_pages = $pages;
        $this->search_allPage = $all_page;

        return $this->search;
    }
    /** Очистка пошукового конструктора */
    public function clear_search():void{ unset($this->search); $this->search = null; }
    /** Отримання валідного посилання на сторінку результата пошуку */
    public function get_searchUrl(bool $clear=true):string|null{
        if($this->search === null) return null;
        $url =  $this->search->get_url();
        if($clear) $this->clear_search();
        return $url;
    }

//#############################################################################//

    /** Отримання информації по классу */
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
            "ver" => $this::ver,
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
}