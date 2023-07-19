<?php

namespace djinni;

class inboxMsgObj{
    public int $id;
    public string $name;
    public string $msg;
    public string $date;
    public string|bool $comments;
    public string $recruiter;
    public inboxMsgObj_company $company;

    public function __construct(){$this->company = new inboxMsgObj_company();}
}
class inboxMsgObj_company{
    public int $id;
    public string $name;
    public string|bool $img;
    public string $url;
    public bool $is_top=false;
}