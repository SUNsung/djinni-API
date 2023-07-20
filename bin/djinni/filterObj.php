<?php

namespace djinni;

class filterObj{
    public branch_filterObj $specialization;
    public branch_filterObj $country;
    public branch_filterObj $city;
    public branch_filterObj $experience;
    public branch_filterObj $employment;
    public branch_filterObj $companyType;
    public branch_filterObj $salaryFrom;
    public branch_filterObj $english;
    public branch_filterObj $others;

    public function __construct(){
        $this->specialization = new branch_filterObj();
        $this->country = new branch_filterObj();
        $this->city = new branch_filterObj();
        $this->experience = new branch_filterObj();
        $this->employment = new branch_filterObj();
        $this->companyType = new branch_filterObj();
        $this->salaryFrom = new branch_filterObj();
        $this->english = new branch_filterObj();
        $this->others = new branch_filterObj();
    }
}
class branch_filterObj{
    public string $name;
    public array $values = [];
}