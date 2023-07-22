<?php

namespace djinni;

class search{
    protected int $page = 1;
    protected const session_uuid="djinni_search_";

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

    /** Добавление параметра [specialization] для поиска */
    public function add_specialization(string $param):self{if(mb_strlen($param)>0) $this->specialization[] = $param; return $this;}
    /** Добавление параметра [country] для поиска */
    public function add_country(string $param):self{if(mb_strlen($param)>0) $this->country[] = $param; return $this;}
    /** Добавление параметра [experience] для поиска */
    public function add_experience(string $param):self{if(mb_strlen($param)>0) $this->experience[] = $param; return $this;}
    /** Добавление параметра [employment] для поиска */
    public function add_employment(string $param):self{if(mb_strlen($param)>0) $this->employment[] = $param; return $this;}
    /** Добавление параметра [companyType] для поиска */
    public function add_companyType(string $param):self{if(mb_strlen($param)>0) $this->companyType[] = $param; return $this;}
    /** Добавление параметра [salaryFrom] для поиска */
    public function add_salaryFrom(string $param):self{if(mb_strlen($param)>0) $this->salaryFrom[] = $param; return $this;}
    /** Добавление параметра [english] для поиска */
    public function add_english(string $param):self{if(mb_strlen($param)>0) $this->english[] = $param; return $this;}
    /** Добавление параметра [others] для поиска */
    public function add_others(string $param):self{if(mb_strlen($param)>0) $this->others[] = $param; return $this;}

    /** Установка страницы */
    public function page(int $page):self{if($page>0) $this->page = $page; return $this;}
    /** Установка ключевых слов для поиска */
    public function anyOfKeywords(string $string):self{if(mb_strlen($string)>0) $this->anyOfKeywords = $string; return $this;}
    /** Установка исключающих ключивых слов */
    public function excludeKeywords(string $string):self{if(mb_strlen($string)>0) $this->excludeKeywords = $string; return $this;}
    /** Поиск по всему тексту вакансии */
    public function fulltext(bool $status):self{$this->fulltext = $status; return $this;}
    /** Поиск только по титулке вакансии */
    public function titleOnly(bool $status):self{$this->titleOnly = $status; return $this;}

    /** Генерирование поисковой строки с параметрами */
    public function get_url():string{

        //Получение кешированого результата
        if($this->is_existsInBuffer()) return $this->get_fromBuffer();

        //Нормальная обработка формированием параметров
        $url = "https://djinni.co/jobs/?all-keywords=";

        //Параметры подробного поиска
        if($this->fulltext !== null)  $url .= "&full_text=".($this->fulltext? "on":"off");
        if($this->titleOnly !== null)  $url .= "&title_only=".($this->titleOnly? "on":"off");

        //Подробный поиск по ключеным словам
        $url .= "&any-of-keywords=".urlencode(string: $this->anyOfKeywords);
        $url .= "&exclude-keywords=".urlencode(string: $this->excludeKeywords);

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

        $this->add_toBuffer(url: $url);
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