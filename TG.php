<?php
require_once __DIR__."/bin/autoload.php";
require_once __DIR__.'/bin/TG_sender.php';

$DJ = new \djinni\Start(mail: sysConstants::$user_email, password: sysConstants::$user_pass);

if(!$DJ->is_auth()){
    $rez = $DJ->auth();
    if (!$rez) sys::print(code: 401, title: "Unauthorized");
}

//Обработка буфера последнего проверенного
$buf_file = __DIR__."/last_id.ttx";
if(!file_exists($buf_file)) file_put_contents($buf_file, "null");
$last_id = file_get_contents($buf_file);

//Загрузка работы
$DJ->start_search(pages: 4)
    ->add_specialization("PHP")->add_salaryFrom("1500")
    ->excludeKeywords("Wordpress Magento Joomla PrestaShop Symfony Shopware")->fulltext(true)
    ->add_english("no_english")->add_english("basic")->add_english("pre")->add_english("intermediate")
    ->add_employment("remote")->add_employment("parttime");
$search = $DJ->load_jobsBySearch();

//Обработка на отправку
$rez = [];
$TG = new TG_sender(url_api: sysConstants::$tg_url_api);
foreach ($search as $job){
    if($job->id !== $last_id){//Отправить если не посленее
        $msg = [];

        $msg[] = "📇 *".$TG->validate($job->job->name)."* 📇".($job->job->salary!==false? "   ".$TG->validate($job->job->salary):"");
        if(isset($job->view)) $msg[] = "👁\ ".$job->view.":".$job->fill;
        $msg[] = "🏢 [".$TG->validate($job->company->name)."](".$job->company->url.")";
        $msg[] = "👔 [".$TG->validate($job->recruiter->name)."](".$job->recruiter->url.") ".$TG->validate($job->recruiter->type);
        $msg[]  = "🌏 ".$TG->validate($job->location);

        //Печать тегов
        foreach ($job->tags as $tag) $msg[] = $TG->validate("     — ".$tag);
        $msg[] = "";

        //Печать описания по абзцам
        foreach ($job->job->description as $text){

            //Перехват списка
            $is_f = substr($text, -1) === ":";

            //ПОдмена апострафа
            $text = str_replace(["&#39;", "&amp;"], ["'", "&"], $text);

            //ПОпытка охватить все списки
            if(!$is_f) if(in_array(substr($text, 0, 1), ["-", "—", "–", "•", "○", "·"])) $text = "    ".$text;

            $text = $TG->validate($text);
            if($is_f) $text = "\n*$text*";
            $msg[] = $text;
        }

        //Формиование кнопок
        $TG->add_inline_urlQuery(" GoTo ", $job->job->url, 0);
        $TG->add_inline_button("❌ Close ❌", "close", 0, 1);
        $TG->add_inline_switchQuery("🗣 Share 🗣", "\n\n".$job->job->name." | ".$job->company->name."\n".$job->job->salary."\n".$job->job->url, 1);

        //Отправка
        $res = $TG->send_msg(chat_id:sysConstants::$tg_user_id, text: join("\n", $msg));
        $rez[] = [
            "code" => $res->code,
            "id" => $job->id
        ];
    }else break;
}

//Обновляем послежний айдишник
file_put_contents($buf_file, $search[0]->id);

sys::print($rez, "SEND TG");

