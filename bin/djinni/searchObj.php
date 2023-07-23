<?php

namespace djinni;

class searchObj{
    public string $id;
    public jobObj $job;
    public string $location;
    public array $tags;
    public inboxMsgObj_company $company;
    public inboxMsgObj_recruiter $recruiter;

    public function __construct(string $url_job){
        $this->id = sha1($url_job);
        $this->job = new jobObj();
        $this->recruiter = new inboxMsgObj_recruiter();
        $this->company = new inboxMsgObj_company();
    }
}

class jobObj{
    public string $name;
    public string $url;
    public string|false $salary;
    public array $description;
}