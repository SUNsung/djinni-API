<?php

namespace djinni;

class inboxMsgObj{
    public int $id;
    public string $name;
    public string $msg;
    public string $date;
    public string|bool $comments;
    public inboxMsgObj_recruiter $recruiter;
    public inboxMsgObj_company $company;

    public function __construct(){
        $this->company = new inboxMsgObj_company();
        $this->recruiter = new inboxMsgObj_recruiter();
    }
}
class inboxMsgObj_company{
    public int $id;
    public string $name;
    public string|bool $img;
    public string $url;
    public bool $is_top=false;
}
class inboxMsgObj_recruiter{
    public string $name;
    public string $type;
    public string $url;
}