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
        $ret = [];

        //Получение первичного отрезка
        $bb = mb_stristr($html_body, "proposals-wrapper");

        //Перебор всех входжений
        while ($bb !== false){

            //Получение точки отчета и смещение
            $bb = mb_stristr($bb, "inbox-row-");
            $bb = mb_substr($bb, 10);

            //обрезка блока до контекстного конца
            $msg = mb_stristr($bb, "js-btn-to-bookmark", true);

            //Удаление лишних символов из верстки
            $msg = explode("\n", $msg);
            foreach ($msg as $pos=>$str) $msg[$pos] = trim($str);
            $msg = join(" ", $msg);

            //Формирование буфера или выход
            if(mb_strlen($msg) > 16)
                $ret[] = $msg;
            else
                break;
        }

        //Отсечение если ничего нет
        if(count($ret) === 0) $ret = [];

        //парсин блоков
        $ret = $this->__parse_inbox_msg($ret);

        \sys::print($ret);

        return $ret;
    }
    /** парсинг полученных блоков на контент */
    private function __parse_inbox_msg(array $array):array{
        $ret_arr = [];

        //Конструктор для получение данных из первичных атрибутов
        function ___get_param(string $name, string $html_content):string{
            $name = $name.'="';

            $string = mb_stristr($html_content, $name);
            $string = mb_substr($string, mb_strlen($name));
            $string = mb_stristr($string, '"', true);

            return $string;
        }

        //Конструктор для получения между блоками
        function ___get_block(string $html_content):string{
            $string = mb_stristr($html_content, ">");
            $string = mb_substr($string, 1);
            $string = mb_stristr($string, "<", true);
            $string = trim($string);

            return $string;
        }

        //перебор блоков
        foreach ($array as $html_content){

            //получение первичных атрибутов
            $id = ___get_param("data-id", $html_content);
            $company_name = ___get_param("data-company", $html_content);
            $company_id = ___get_param('data-company-id', $html_content);

            //Получение юзерпика если есть
            $userpic = mb_stristr($html_content, "userpic-image_img");
            if($userpic !== false) $userpic = ___get_param("src", $userpic);

            //получение ссылки на фирму
            $html_content = mb_stristr($html_content, "recruiter-name");
            $recruiter_url = "https://djinni.co".___get_param("href", $html_content);

            //получение имени рекрутера
            $recruiter = mb_stristr($html_content, "recruiter-headline");
            $recruiter = ___get_block($recruiter);

            //получение статуса топ работодателя
            $award = mb_stristr($html_content, "award-fill");
            if($award !== false) $award = true;

            //Обрезка контента
            $html_content = mb_stristr($html_content, "/my/inbox/$id/");
            $html_content = mb_stristr($html_content, "/my/inbox/$id/#reply", true);

            //получение коментарии если есть
            $comments = ___get_block($html_content);
            if($comments === "") $comments = false;


            //титулка работы
            $work = mb_stristr($html_content, "to-job");
            $work = ___get_block($work);

            //тело последнего собщения
            $msg = mb_stristr($html_content, "message-text-inner");
            $msg = "<a".mb_stristr($msg, ">");
            $msg = strip_tags($msg);
            $msg = trim($msg);

            $msg_obj = new inboxMsgObj();

            $msg_obj->id = $id;
            $msg_obj->msg = $msg;
            $msg_obj->name = $work;
            $msg_obj->comments = $comments;
            $msg_obj->recruiter = $recruiter;

            $msg_obj->company->id = $company_id;
            $msg_obj->company->name = $company_name;
            $msg_obj->company->url = $recruiter_url;
            $msg_obj->company->img = $userpic;
            $msg_obj->company->is_top = $award;


            $ret_arr[] = $msg_obj;
        }

        return $ret_arr;
    }

}