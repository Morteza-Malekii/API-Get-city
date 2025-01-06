<?php
namespace app\services;
class CityService{

    public function getCities($data){
        $result = getCities($data);
         return $result;
    }

    public function createCity($data){
        $result = addCity($data);
         return $result;
    }
    public function updateCityName($city_id,$city_name){
        $result = changeCityName($city_id,$city_name);
         return $result;
    }
    public function deletCity($city_id){
        $result = deleteCity($city_id);
         return $result;
    }

}