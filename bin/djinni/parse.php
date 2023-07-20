<?php

namespace djinni;

class parse{

    /** Получение токена из тела запроса */
    protected function parse_csrfmiddlewaretoken(string $html_body):string{
        preg_match('/<input type=\'hidden\' name=\'csrfmiddlewaretoken\' value=\'[A-Za-z0-9]*\'/u',$html_body,$result);
        preg_match('/\'[A-Za-z0-9]*\'$/u',$result[0],$result1);

        return str_replace("'", '', $result1[0]);
    }


    //todo долина костылей

    /** получение сообщений из тела сообщений */
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
            $msg_obj->recruiter = $recruiter;
            $msg_obj->date = $datetime;

            $msg_obj->company->id = intval($company_id);
            $msg_obj->company->name = $company_name;
            $msg_obj->company->url = $recruiter_url;
            $msg_obj->company->img = $userpic;
            $msg_obj->company->is_top = $award;


            $ret_arr[] = $msg_obj;
        }

        return $ret_arr;
    }

    /** парсинг страницы на фильтры для поиска */
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
        if(count($buf_arr) !== 9) \sys::print(code: 500, title: "Failed to get search parameters");

        //Перебор буфера для получения фактических обьектов
        foreach ($buf_arr as $pos=>$bom){
            switch ($pos){

                //specialization
                case 0:
                    $ret->specialization->name = $bom["name"];
                    $ret->specialization->values = $this->___get_paramsArr("primary_keyword", $bom["html"]);
                    break;

                //country
                case 1:
                    $ret->country->name = $bom["name"];
                    $ret->country->values = $this->___get_paramsArr("region", $bom["html"]);
                    break;

                //city
                case 2:
                    $ret->city->name = $bom["name"];
                    $ret->city->values = $this->___get_paramsArr("location", $bom["html"]);
                    break;

                //experience
                case 3:
                    $ret->experience->name = $bom["name"];
                    $ret->experience->values = $this->___get_paramsArr("exp_level", $bom["html"]);
                    break;

                //employment
                case 4:
                    $ret->employment->name = $bom["name"];
                    $ret->employment->values = $this->___get_paramsArr("employment", $bom["html"]);
                    break;

                //company_type
                case 5:
                    $ret->companyType->name = $bom["name"];
                    $ret->companyType->values = $this->___get_paramsArr("company_type", $bom["html"]);
                    break;

                //salary
                case 6:
                    $ret->salaryFrom->name = $bom["name"];
                    $ret->salaryFrom->values = $this->___get_paramsArr("salary", $bom["html"]);
                    break;

                //english_level
                case 7:
                    $ret->english->name = $bom["name"];
                    $ret->english->values = $this->___get_paramsArr("english_level", $bom["html"]);
                    break;

                //editorial
                case 8:
                    $ret->others->name = $bom["name"];
                    $ret->others->values = $this->___get_paramsArr("editorial", $bom["html"]);
                    break;

            }
        }

        \sys::print($ret);


        return $ret;
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