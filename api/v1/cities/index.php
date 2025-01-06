<?php

include_once ($_SERVER['DOCUMENT_ROOT'].'/My-project/Iran/loader.php');


// echo 'city endpoint is here';


use app\services\CityService;
use app\services\cityValidation;
use app\services\ProvinceService;
use app\utilities\Response;
use app\utilities\cacheUtility;

#================  chech Authorization by jwt  =================
$token = getBearerToken();
if (!$token)
    Response::respondAndDie("Authorization is required",Response::HTTP_UNAUTHORIZED);
$user = isValidToken($token);
if (!$user)
    Response::respondAndDie("User not found",Response::HTTP_UNAUTHORIZED);

// Response::respondAndDie($user,Response::HTTP_UNAUTHORIZED);


$request_body = json_decode(file_get_contents('php://input'),true);

$request_method = $_SERVER['REQUEST_METHOD'];

$city_service = new CityService();
$isValidCity = new cityValidation();

switch ($request_method) {
    case 'GET':
        if(!hasAccessToProvince($user,$_GET['province_id']))
            Response::respondAndDie("You have no access to this provinces!",Response::HTTP_FORBIDDEN);
    
    cacheUtility::start();
    $province_id = $_GET['province_id'] ;
        if (!$isValidCity->provinceIdValidation($province_id)){
            Response::respondAndDie("province_id is invalid",Response::HTTP_BAD_REQUEST);
        }
        $request_data = [
            'province_id' => $province_id,
            'page' => $_GET['page'] ?? null,
            'pagesize' => $_GET['pagesize'] ?? null,
            'field'=> $_GET['field'] ?? null,
            'orderby'=> $_GET['orderby'] ?? null
        ];
        $response =$city_service->getCities($request_data);
        if (empty($response))
            Response::respondAndDie("no data found",Response::HTTP_NOT_FOUND);
        echo Response::respond($response,Response::HTTP_OK);
        cacheUtility::end();
        die();

        case 'POST':
            if (!$isValidCity->cityValidation($request_body))
            Response::respondAndDie("request is invalid",Response::HTTP_BAD_REQUEST);
        
        $response = $city_service->createCity($request_body);
        Response::respondAndDie($response,Response::HTTP_CREATED);

    case 'PUT':
        [$city_id,$city_name]=[$request_body['city_id'],$request_body['city_name']];

        if(!$isValidCity->updateCityValidation($city_id,$city_name))
            Response::respondAndDie("request is invalid",Response::HTTP_BAD_REQUEST);

        $response = $city_service->updateCityName($city_id,$city_name);
        Response::respondAndDie($response,Response::HTTP_OK);
    case 'DELETE':
        $city_id = $_GET['city_id'] ?? null;
        if (!$isValidCity->isCityIdExists($city_id))
            Response::respondAndDie("city_id is not found",Response::HTTP_NOT_FOUND);
        $response = $city_service->deletCity($city_id);
        Response::respondAndDie($response,Response::HTTP_OK);
    
    
    default:
        Response::respondAndDie("method in invalid",Response::HTTP_METHOD_NOT_ALLOWED);
        break;
}

// $cs = new CityService();
// $result = $cs->getCities((object)[12,55,62,891]);
// Response::respondAndDie($result,Response::HTTP_OK);





