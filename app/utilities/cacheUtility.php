<?php
namespace app\utilities;

use \Exception;
use app\utilities\Response;

class cacheUtility
{
    protected static $cachefile;
    protected static$cache_enabled = CHACHE_ENABLED;
    const EXPIRE_TIME = 3600;

    public static function init()
    {
        self::$cachefile = CACHE_DIR . md5($_SERVER['REQUEST_URI']) . '.json';
        if ($_SERVER['REQUEST_METHOD'] != 'GET'){
            self::$cache_enabled = 0;
        }
    }

    public static function cache_exist(){
        return ((file_exists(self::$cachefile) && (time()-self::EXPIRE_TIME < filemtime(self::$cachefile))));
    }

    public static function start(){
        self::init();

        if(!self::$cache_enabled)
        return ;
        if (self::cache_exist()){
            Response::setHeaders();
            readfile(self::$cachefile);
            exit;
        }
        ob_start();
    }

    public static function end(){
        if(!self::$cache_enabled)
            return ;
        // $cached = fopen(self::$cachefile,'w');
        // fwrite($cached,ob_get_contents());
        // fclose($cached);
        file_put_contents(self::$cachefile,ob_get_contents());
        ob_end_flush();
    }


    public static function flush(){
        $files = glob(CACHE_DIR.'*');
        foreach ($files as $file){
            if(is_file($file))
                unlink($file);
        }
    }

    public static function delete($key){
        $file = CACHE_DIR.md5($key).'.json';
        if(file_exists($file))
            unlink($file);
    }
}