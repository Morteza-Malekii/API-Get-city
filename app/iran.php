<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

#================  Database Connection  =================
try {
    $pdo = new PDO("mysql:dbname=Iran;host=127.0.0.1", 'root', '');
    $pdo->exec("set names utf8;");
    // echo "Connection OK!";
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

#==============  Simple Validators  ================

function isProvinceIdExists($province_id) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM province WHERE id = :province_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':province_id' => $province_id]);
    return $stmt->fetchColumn() > 0;
}
function isCityIdExists($city_id) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM city WHERE id = :city_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':city_id' => $city_id]);
    return $stmt->fetchColumn() > 0;
}

function isValidCity($data){
    if(empty($data['province_id']) or !is_numeric($data['province_id']) or !isProvinceIdExists($data['province_id']) or isCityNameExists($data['name']))
        return false;
    return empty($data['name']) ? false : true;
}

function isValidProvince($data){
    return empty($data['name']) ? false : true;
}

function isCityNameExists($name){
    global $pdo;
    $sql = "SELECT COUNT(*) FROM city WHERE name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':name' => $name]);
    return $stmt->fetchColumn() > 0;
}
function isUpdateCityName($city_id,$city_name){
    global $pdo;
    $sql = "SELECT COUNT(*) FROM city WHERE name = :name and id != :city_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':name' => $city_name,':city_id' => $city_id]);
    return $stmt->fetchColumn() > 0;
}

#================  Read Operations  =================
function getCities($data = null){
    global $pdo;
    $province_id = (int)$data['province_id'] ?? null;
    $field = $data['field'] ?? '*';
    $orderby = $data['orderby'] ?? '';
    $page = (int)$data['page'] ;
    $pagesize = (int)$data['pagesize'];
    if($page==null or $pagesize==null){
        $limit = '';
    }else{
        $offset = ($page - 1) * $pagesize;
        $limit = "limit $offset,$pagesize";
    }
    $where = '';
    if(!is_null($province_id) and is_int($province_id)){
        $where = "where province_id = {$province_id} ";
    }
    if($field != '*'){
        $allowedFields = ['id', 'province_id', 'name']; // Add all allowed fields here
        $fields = explode(',', $field);
        $fields = array_intersect($fields, $allowedFields);
        if (empty($fields)) {
            $field = '*';
        } else {
            $field = implode(',', array_map(function($f) { return "`$f`"; }, $fields));
        }
    }
    if (!empty($orderby)) {
        $orderbystr = "order by $orderby";
    } else {
        $orderbystr = '';
    }
    
    $sql = "select $field from city $where $orderbystr $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $records;
}
function getProvinces($data = null){
    global $pdo;
    $sql = "select * from province";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $records;
}


#================  Create Operations  =================
function addCity($data){
    global $pdo;
    if(!isValidCity($data)){
        return false;
    }
    $sql = "INSERT INTO `city` (`province_id`, `name`) VALUES (:province_id, :name);";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':province_id'=>$data['province_id'],':name'=>$data['name']]);
    return $stmt->rowCount();
}
function addProvince($data){
    global $pdo;
    if(!isValidProvince($data)){
        return false;
    }
    $sql = "INSERT INTO `province` (`name`) VALUES (:name);";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':name'=>$data['name']]);
    return $stmt->rowCount();
}


#================  Update Operations  =================
function changeCityName($city_id,$name){
    global $pdo;
    $sql = "update city set name = '$name' where id = $city_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount();
}
function changeProvinceName($province_id,$name){
    global $pdo;
    $sql = "update province set name = '$name' where id = $province_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount();
}

#================  Delete Operations  =================
function deleteCity($city_id){
    global $pdo;
    $sql = "delete from city where id = $city_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount();
}
function deleteProvince($province_id){
    global $pdo;
    $sql = "delete from province where id = $province_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount();
}

#================  User Operations  =================
$userdata = [
(object)['id'=>1,'name'=>'Ali','email'=>'Ali145@gmail.com','role' =>'user','allowed_provinces' => [1,2,3]],
(object)['id'=>2,'name'=>'morteza','email'=>'morteza167@gmail.com','role' =>'admin','allowed_provinces' => []],
(object)['id'=>3,'name'=>'Mina','email'=>'mina@gmail.com','role' =>'user','allowed_provinces' => [1,2,3]],
(object)['id'=>4,'name'=>'Sara','email'=>'','role' =>'user','allowed_provinces' => [1,2,3]],
(object)['id'=>5,'name'=>'Mehdi','email'=>'','role' =>'user','allowed_provinces' => [1,2,3]]
];

function getUserById($id){
    global $userdata;
    foreach($userdata as $user)
        if($user->id==$id)
            return $user;
    return null;
}

function getUserByEmail($email){
    global $userdata;
    foreach($userdata as $user)
        if($user->email == $email)
            return $user;
    return null;
    
}  
#================  JWT Operations  =================

function createJwtToken($user){
    $issuedAt = new DateTimeImmutable();
    $notBefore = $issuedAt->modify('-1 minute')->getTimestamp(); // یک دقیقه قبل
    $expiration = $issuedAt->modify('+1 hour')->getTimestamp(); // یک ساعت بعد
    $payload = [
        'iss' => 'http://mrt.com',
        'aud' => 'http://mrt.com',
        'iat' => $issuedAt->getTimestamp(), // زمان صدور
        'nbf' => $notBefore, // زمان معتبر شدن
        'exp' => $expiration, // زمان انقضا
        'id'=> $user->id,
    ];
    
    return JWT::encode($payload, JWT_KEY, JWT_ALG);
}

/** 
 * Get header Authorization
 * */
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function isValidToken($token){
    try {
        $now = new DateTimeImmutable();
        $payload = JWT::decode($token, new Key(JWT_KEY , JWT_ALG));
        if($payload->exp < $now->getTimestamp())
            return false;
        // return $payload;
        $user = getUserById($payload->id);
        return $user;
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}

function hasAccessToProvince($user,$province_id){
    return (in_array($province_id,$user->allowed_provinces) or $user->role == 'admin');
}




// Function Tests
// $data = addCity(['province_id' => 23,'name' => "Loghman Shahr"]);
// $data = addProvince(['name' => "7Learn"]);
//  $data = getCities(['province_id' => 1]);
// $data = deleteProvince(34);
// $data = changeProvinceName(34,"سون لرن");
// $data = getProvinces();
// $data = deleteCity(443);
// $data = changeCityName(445,"لقمان شهر");
// $data = getCities(['province_id' => 1]);
// $data = json_encode($data);
// echo "<pre>";
// print_r($data);
// echo "</pre>";
