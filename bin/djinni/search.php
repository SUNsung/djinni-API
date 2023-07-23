<?php

namespace djinni;

class search{
    protected int $page = 1;
    protected const session_uuid="djinni_search_";
    protected const buffering = false;

    protected string $anyOfKeywords="";
    protected string $excludeKeywords="";
    protected ?bool $fulltext = null;
    protected ?bool $titleOnly =null;

    protected array $specialization=[];     //primary_keyword
    protected array $country=[];            //region
    protected array $city=[];               //location
    protected array $experience=[];         //exp_level
    protected array $employment=[];         //employment
    protected array $companyType=[];        //company_type
    protected array $salaryFrom=[];         //salary
    protected array $english=[];            //english_level
    protected array $others=[];             //editorial

    /** Добавання параметра [specialization] для пошуку */
    public function add_specialization(string $param):self{if(mb_strlen($param)>0) $this->specialization[] = $param; return $this;}
    /** Добавання параметра [country] для пошуку */
    public function add_country(string $param):self{if(mb_strlen($param)>0) $this->country[] = $param; return $this;}
    /** Добавання параметра [city] для пошуку */
    public function add_city(string $param):self{if(mb_strlen($param)>0) $this->city[] = $param; return $this;}
    /** Добавання параметра [experience] для пошуку */
    public function add_experience(string $param):self{if(mb_strlen($param)>0) $this->experience[] = $param; return $this;}
    /** Добавання параметра [employment] для пошуку */
    public function add_employment(string $param):self{if(mb_strlen($param)>0) $this->employment[] = $param; return $this;}
    /** Добавання параметра [companyType] для пошуку */
    public function add_companyType(string $param):self{if(mb_strlen($param)>0) $this->companyType[] = $param; return $this;}
    /** Добавання параметра [salaryFrom] для пошуку */
    public function add_salaryFrom(string $param):self{if(mb_strlen($param)>0) $this->salaryFrom[] = $param; return $this;}
    /** Добавання параметра [english] для пошуку */
    public function add_english(string $param):self{if(mb_strlen($param)>0) $this->english[] = $param; return $this;}
    /** Добавання параметра [others] для пошуку */
    public function add_others(string $param):self{if(mb_strlen($param)>0) $this->others[] = $param; return $this;}

    /** Установка сторінки з якої почати пошук */
    public function page(int $page):self{if($page>0) $this->page = $page; return $this;}
    public function get_page():int{return $this->page;}
    /** Установка ключевих слів для пошуку */
    public function anyOfKeywords(string $string):self{if(mb_strlen($string)>0) $this->anyOfKeywords = $string; return $this;}
    /** Установка виключающих слів слов */
    public function excludeKeywords(string $string):self{if(mb_strlen($string)>0) $this->excludeKeywords = $string; return $this;}
    /** пошук по всьому тексту вакансії */
    public function fulltext(bool $status):self{$this->fulltext = $status; return $this;}
    /** пошук тільки по назві вакансії */
    public function titleOnly(bool $status):self{$this->titleOnly = $status; return $this;}

    /** Генерування пощукової адреси */
    public function get_url():string{

        //Получение кешированого результата
        if($this::buffering) if($this->is_existsInBuffer()) return $this->get_fromBuffer();

        //Нормальная обработка формированием параметров
        $url = "https://djinni.co/jobs/?all-keywords=";

        //Параметры подробного поиска
        if($this->fulltext !== null)  $url .= "&full_text=".($this->fulltext? "on":"off");
        if($this->titleOnly !== null)  $url .= "&title_only=".($this->titleOnly? "on":"off");

        //Подробный поиск по ключеным словам
        $url .= "&any-of-keywords=".urlencode(string: $this->anyOfKeywords);
        $url .= "&exclude-keywords=".urlencode(string: $this->excludeKeywords);

        //новый функционал (?????) исключаюшие слова нужно отдельно прописывать
        if($this->excludeKeywords !== ""){
            $bbb = explode(" ", $this->excludeKeywords);
            $url .= "&keywords=";
            foreach ($bbb as $param) $url .= "-".$param."+";
        }

        //Отсечение городов если нет Украины
        if(count(value: $this->city) > 0) if(!in_array(needle: "UKR", haystack: $this->country)) $this->city = [];

        //Массив связности парсера
        $key_arr = [
            ["specialization", "primary_keyword"],
            ["country", "region"],
            ["city", "location"],
            ["experience", "exp_level"],
            ["employment", "employment"],
            ["companyType", "company_type"],
            ["salaryFrom", "salary"],
            ["english", "english_level"],
            ["others", "editorial"]
        ];

        //Формирование парметров из массивов
        foreach ($key_arr as $pp){
            foreach ($this->{$pp[0]} as $param) $url .= "&".$pp[1]."=".trim(string: $param);
        }

        //Указатель на страницу
        if($this->page < 1) $this->page = 1;
        if($this->page > 1) $url .= "&page=".$this->page;

        if($this::buffering)$this->add_toBuffer(url: $url);
        return $url;
    }

    private function get_uuidRequest():string{
        return base64_encode(string: sha1(string: json_encode(value: [
            $this->page,
            $this->anyOfKeywords,
            $this->excludeKeywords,
            $this->fulltext,
            $this->titleOnly,
            $this->specialization,
            $this->country,
            $this->city,
            $this->experience,
            $this->employment,
            $this->companyType,
            $this->salaryFrom,
            $this->english,
            $this->others
        ]), binary: true));
    }
    private function is_existsInBuffer():bool{return isset($_SESSION[$this::session_uuid.$this->get_uuidRequest()]);}
    private function add_toBuffer(string $url):void{$_SESSION[$this::session_uuid.$this->get_uuidRequest()] = $url;}
    private function get_fromBuffer():string|null{return $_SESSION[$this::session_uuid.$this->get_uuidRequest()];}
}