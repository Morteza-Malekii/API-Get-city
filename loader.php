<?php
include_once __DIR__.'/vendor/autoload.php';
include_once __DIR__.'/app/iran.php';
define('CACHE_DIR',__DIR__.'/cache/');
define('CHACHE_ENABLED',0);

#================ jwt constants ================
define ('JWT_KEY','mrt-kjbuifsbsbv618135');
define ('JWT_ALG','HS256'); 

function my_autoload($class){
    $dirclass = str_replace('\\',DIRECTORY_SEPARATOR,$class);
    $classFile = __DIR__.DIRECTORY_SEPARATOR.$dirclass.'.php';
    if (!(file_exists($classFile)&& is_readable($classFile))){
        die('Class File not found !');
    }
    include $classFile;
    
}

spl_autoload_register('my_autoload');
