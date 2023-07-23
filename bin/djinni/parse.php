<?php

namespace djinni;

class parse{

    /** Отримання токена із тіла запроса */
    protected function parse_csrfmiddlewaretoken(string $html_body):string{
        preg_match('/<input type=\'hidden\' name=\'csrfmiddlewaretoken\' value=\'[A-Za-z0-9]*\'/u',$html_body,$result);
        preg_match('/\'[A-Za-z0-9]*\'$/u',$result[0],$result1);

        return str_replace("'", '', $result1[0]);
    }


    /** парсинг  листів */
    protected function parse_inbox_msg(string $html_body):array{

        //ПОлучение блоков сообщений
        $ret = $this->__get_array("inbox-row-", "inbox-read-icons", $html_body);

        //парсин блоков
        $ret = $this->__parse_inbox_msg($ret);

        return $ret;
    }
    private function __parse_inbox_msg(array $array):array{
        $ret_arr = [];

        //перебор блоков
        foreach ($array as $html_content){

            //получение первичных атрибутов
            $id = $this->___get_param("data-id", $html_content);
            $company_name = $this->___get_param("data-company", $html_content);
            $company_id = $this->___get_param('data-company-id', $html_content);

            //Получение юзерпика если есть
            $userpic = mb_stristr($html_content, "userpic-image_img");
            if($userpic !== false) $userpic = $this->___get_param("src", $userpic);

            //Получение даты сообщения
            $datetime = mb_stristr($html_content, "inbox-date");
            $datetime = $this->___get_block($datetime);

            //получение ссылки на фирму
            $html_content = mb_stristr($html_content, "recruiter-name");
            $recruiter_url = "https://djinni.co".$this->___get_param("href", $html_content);

            //получение имени рекрутера
            $recruiter = mb_stristr($html_content, "recruiter-headline");
            $recruiter = $this->___get_block($recruiter);

            //получение статуса топ работодателя
            $award = mb_stristr($html_content, "award-fill");
            if($award !== false) $award = true;

            //титулка работы
            $work = mb_stristr($html_content, "to-job");
            $work = $this->___get_block($work);

            //Обрезка контента
            $buf_html = mb_stristr($html_content, "/my/inbox/$id/#reply", true);
            if(is_string($buf_html)) $html_content = $buf_html;

            //тело последнего собщения
            $msg = mb_stristr($html_content, "message-text-inner");
            $msg = "<a".mb_stristr($msg, ">");
            $msg = strip_tags($msg);
            $msg = trim($msg);

            //получение коментарии если есть
            if($buf_html !== false){//Обработка только инбокса
                $html_content = mb_stristr($html_content, "/my/inbox/$id/");
                $comments = $this->___get_block($html_content);
                if($comments === "") $comments = false;
            }else
                $comments = false;//если архив то нет ничего

            $msg_obj = new inboxMsgObj();

            $msg_obj->id = intval($id);
            $msg_obj->msg = $msg;
            $msg_obj->name = $work;
            $msg_obj->comments = $comments;
            $msg_obj->date = $datetime;

            $recruiter = explode(",", $recruiter);
            $msg_obj->recruiter->name = trim($recruiter[0]);
            $msg_obj->recruiter->type = trim($recruiter[1]);

            $msg_obj->company->id = intval($company_id);
            $msg_obj->company->paramId = trim(explode("?company=", $recruiter_url)[1]);
            $msg_obj->company->name = $company_name;
            $msg_obj->company->url = $recruiter_url;
            $msg_obj->company->img = $userpic;
            $msg_obj->company->is_top = $award;


            $ret_arr[] = $msg_obj;
        }

        return $ret_arr;
    }

    /** парсинг дерева рааметрів для пошуку */
    protected function parse_jobs_filter(string $html_body):filterObj{

        //ПОлучение блоков сообщений
        $ret = $this->__get_array("jobs-filter__set-title", "jobs-filter__set", $html_body, "jobs-filter__btns");

        //Порсинг блоков и возврат
        return $this->__parse_jobs_filter($ret);
    }
    private function __parse_jobs_filter(array $array):filterObj{
        $ret = new filterObj();
        $buf_arr = [];

        //Отсечение если ничего нет
        if(count($array) === 0) return $ret;

        //перебор блоков для получение первичной выборки
        foreach ($array as $html_content){

            //Отсечение первых блоков с поиском
            if(mb_stristr($html_content, 'inputmode="search"') === false){
                $short = strip_tags(mb_substr($html_content, 2));
                $short = trim($short);

                //Отсечение по названию
                if(mb_strlen($short) < 40){
                    $buf_arr[] = ["name"=>$short, "html"=>""];
                    continue;
                }

                //Добавление в буфер блоков
                $buf_arr[count($buf_arr)-1]["html"] .= "  ".$html_content;
            }
        }

        //Отсечение если сломался парсер верстки
        if(count($buf_arr) !== 9) throw new \ErrorException("Failed to get search parameters");

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

        //Перебор буфера для получения фактических обьектов
        foreach ($buf_arr as $pos=>$bom){
            $ret->{$key_arr[$pos][0]}->name = $bom["name"];
            $ret->{$key_arr[$pos][0]}->values = $this->___get_paramsArr($key_arr[$pos][1], $bom["html"]);
        }

        return $ret;
    }

    /** парсинг останіх візіторов */
    protected function parse_profileView(string $html_body):array{

        //Обрезка контента до нужного блока
        $html_body = mb_stristr($html_body, "views-wrapper");
        $html_body = mb_stristr($html_body, "<form", true);

        //парсин блоков
        $ret = $this->__get_array('<table class="table">', "</table>", $html_body);
        return $this->__parse_profileView($ret);
    }
    private function __parse_profileView(array $array):array{
        $ret = [];
        foreach ($array as $htm){

            //Разбиение на основное
            $htm = mb_stristr($htm, '<span class="recruiter-name">');
            $htm = explode("<br>", $htm);

            //Отсечение указания на фирму
            $htm[1] = explode("at", $htm[1])[0];

            //todo костыль пока есть автозагрущик
            new inboxMsgObj();

            //Формирование обьекта рекрутера
            $rec = new inboxMsgObj_recruiter();
            $rec->name = trim(strip_tags($htm[0]));
            $rec->type = trim(strip_tags($htm[1]));
            $rec->url = "https://djinni.co".$this->___get_param("href", $htm[0]);
            $rec->date = trim(strip_tags($htm[2]));

            $ret[] = $rec;
        }

        return $ret;
    }


    /** Парсинг номерів сторінок для пошуку */
    protected function parse_search_pages(string $html):array{
        $pagination = mb_stristr($html, 'class="pagination');
        $pagination = mb_stristr($pagination, "</ul>", true);
        $pagination = $this->__get_array('class="page-item', "</li>", $pagination);

        //Плучение страниц с пагинатора
        $pages = [];
        foreach ($pagination as $htm){
            $htm = intval(strip_tags('<"'.$htm));
            if($htm > 0) $pages[] = $htm;
        }

        return $pages;
    }
    /** Парсинг вакансій */
    protected function parse_search_content(string $html_body):array{return $this->__blocks_parse_search_content($html_body);}
    private function __blocks_parse_search_content(string $html):array{
        $rez = [];
        $load_arr = $this->__get_array('class="list-jobs__item', 'class="list-jobs__item', $html, 'class="pagination');

        //Перебор всех блоков
        foreach ($load_arr as $htm){
            $htm = mb_stristr($htm, 'nowrap">');

            //Время публикации и Количество просмотров
            $time = $this->___get_block($htm);
            $see = $this->___get_param("title", $htm);

            //Количество 2 отозвавщихся
            $htm = mb_stristr($htm, $see);
            $echo = $this->___get_param("title", $htm);

            //url вакансии
            $htm = mb_stristr($htm, '<a class="profile"');
            $job_url = $this->___get_param("href", $htm);

            //Имя вакансии
            $htm = mb_stristr($htm, 'span');
            $job_name = $this->___get_block($htm);

            //Зарплатная вилка если есть
            $salary = mb_stristr($htm, 'public-salary-item');
            if(is_string($salary)) $salary = $this->___get_block($salary);

            //Получение описания из блока абзацами
            $htm = mb_stristr($htm, 'text-card');
            $buf_description = mb_stristr($htm, '>');
            $buf_description = mb_substr($buf_description, 1);
            $buf_description = mb_stristr($buf_description, '</div>', true);
            $buf_description = explode("<br>",  $buf_description);
            $job_description = [];
            foreach ($buf_description as $text){
                $text = strip_tags($text);
                $text = trim($text);
                if(mb_strlen($text)>0) $job_description[] = $text;
            }

            //лого компании если есть
            $company_img = mb_stristr($htm, "userpic-image_img");
            if(is_string($company_img)) $company_img = $this->___get_param("src", $company_img);

            //url фирмы
            $htm = mb_stristr($htm, 'list-jobs__details__info');
            $company_url = $this->___get_param("href", $htm);

            //имя фирмы
            $htm = mb_stristr($htm, $company_url);
            $company_name = $this->___get_block($htm);

            //url рекрутера
            $htm = mb_stristr($htm, $company_name);
            $recruter_url = $this->___get_param("href", $htm);

            //имя рекрутера
            $htm = mb_stristr($htm, $recruter_url);
            $recruter_name = $this->___get_block($htm);

            //Получение локации
            $xxx = mb_stristr($htm, '<span class="location-text">');
            $xxx = mb_stristr($xxx, "<nobr", true);
            $xxx = strip_tags($xxx);
            $xxx = trim($xxx);

            //ПОдучение доппараметров
            $yyy = [];
            foreach ($this->__get_array("<nobr", "</nobr>", $htm) as $text){
                $text = strip_tags("<".$text);
                $text = trim($text, " ·\r\0\t");
                if(mb_strlen($text)>0) $yyy[] = $text;
            }


            //Формирование обьектов на выдачу
            $search = new searchObj($job_url);

            $recruter_name = explode(",", $recruter_name);
            $search->recruiter->name = trim($recruter_name[0]);
            $search->recruiter->type = trim($recruter_name[1]);
            $search->recruiter->url = "https://djinni.co".$recruter_url;

            $search->company->paramId = trim(explode("?company=", $company_url)[1]);
            $search->company->name = $company_name;
            $search->company->url  = 'https://djinni.co'.$company_url;
            $search->company->img = $company_img;

            $search->job->url = 'https://djinni.co'.$job_url;
            $search->job->name = $job_name;
            $search->job->salary = $salary;
            $search->job->description = $job_description;

            $search->location = $xxx;
            $search->tags = $yyy;
            $search->view = intval($see);
            $search->fill = intval($echo);
            $search->date = $time;

            //Запись в буфер
            $rez[] = $search;
        }

        return $rez;
    }

private function ___get_paramsArr(string $param, string $html_content):array{
        $ret_arr = [];

        //Разбиение на подблоки
        $buf_arr = explode('name="'.$param.'"', $html_content);
        unset($buf_arr[0]);

        //Перебор подблоков и получение данных
        foreach ($buf_arr as $htm){
            $htm = explode('class="  "', $htm)[0];

            //Формирование выдачи
            $ret_arr[] = (object)[
                "name" => trim(strip_tags("<a".$htm)),
                "key" => $this->___get_param("value", $htm)
            ];
        }

        return $ret_arr;
    }
private function __get_array(string$begin_param, string $end_param, string $html_content, string $last=""):array{
    $ret = [];

    //Получение первичного отрезка
    $bb = mb_stristr($html_content, $begin_param);
    $length = mb_strlen($begin_param);
    $last_block = "";

    //Перебор всех входжений
    while ($bb !== false){

        //Получение точки отчета и смещение
        $bb = mb_stristr($bb, $begin_param);
        $bb = mb_substr($bb, $length);

        //обрезка блока до контекстного конца
        $msg = mb_stristr($bb, $end_param, true);
        if($msg === false) break;
        $last_block = $msg;

        //Удаление лишних символов из верстки
        $msg = $this->___clear_html($msg);

        //Формирование буфера или выход
        if(mb_strlen($msg) > 16)
            $ret[] = $msg;
        else
            break;
    }

    //Обработка хвостового если нужно
    if($last !== ""){
        $bb = mb_stristr($html_content, $last_block);
        $bb = mb_substr($bb, mb_strlen($last_block));
        $bb = mb_stristr($bb, $last, true);
        $ret[] = $this->___clear_html($bb);
    }

    return $ret;
}
private function ___get_param(string $name, string $html_content):string{//Конструктор для получение данных из первичных атрибутов
        $name = $name.'="';

        $string = mb_stristr($html_content, $name);
        $string = mb_substr($string, mb_strlen($name));
        $string = mb_stristr($string, '"', true);

        return $string;
    }
private function ___get_block(string $html_content):string{//Конструктор для получения между блоками
        $string = mb_stristr($html_content, ">");
        $string = mb_substr($string, 1);
        $string = mb_stristr($string, "<", true);
        $string = trim($string);

        return $string;
    }
private function ___clear_html(string $html):string{
    $html = explode("\n", $html);
    foreach ($html as $pos=>$str) $html[$pos] = trim($str);
    $html = join(" ", $html);

    return $html;
    }

}