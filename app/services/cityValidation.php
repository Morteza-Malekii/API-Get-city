<?php

namespace app\services;

class cityValidation {

    public function provinceIdValidation($province_id) {
     if (!isset($province_id) or !is_numeric($province_id) or !isProvinceIdExists($province_id)){
            return false;
    }else {
        return true;
    }
    }

    public function cityValidation($name) {
        if (!isValidCity($name)){
            return false;
        }else {
            return true;
        }
    }

    public function updateCityValidation($city_id,$city_name) {
        if (isUpdateCityName($city_id,$city_name)){
            return false;
        }else {
            return true;
        }
    }
    public function isCityIdExists($city_id) {
        if (!isCityIdExists($city_id)){
            return false;
        }else {
            return true;
        }
    }

}    

