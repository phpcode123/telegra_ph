<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Db;
use GeoIp2\Database\Reader;


class Otherclass  extends BaseController
{

    public static function getRedisStatus($key){
        $redis = new \Redis();
        $redis->connect(Config::get("app.redis_host"), Config::get("app.redis_post"));


        if($redis->exists($key)){
            $status = 1;
        }else{
            $status = 0;
        }
        return $status;
    }


    //overtime超时时间单位为秒，如果为空则不是设置超时时间，即永久有效
    public static function setRedisValue($key, $value=1, $overtime=0){
        $redis = new \Redis();
        $redis->connect(Config::get("app.redis_host"), Config::get("app.redis_post"));
        if($redis->set($key, $value)){

            //设置超时时间
            if($overtime > 0){
                //expireat单位为秒，expireAt单位为毫秒
                $redis->Expire($key, $overtime);
            }


            return 1;
        }else{
            return 0;
        }
    }

    public static function getRedisValue($key){
        $redis = new \Redis();
        $redis->connect(Config::get("app.redis_host"), Config::get("app.redis_post"));
        if($redis->exists($key)){
            $value = $redis->get($key);
            
            return $value;
        }else{
            return 0;
        }
    }



    //返回随机字符串
    public static function getShortUrlStr() {


        $shortUrlStr = "";
        
        //随机生成32-50位长度的字符串，然后从0-6开始截取字符串去数据库中查询，如果能匹配到则自动增加1，直到匹配不到数据为止。
        //随机字符串不要太长，会占用cpu性能
        $num = mt_rand(32,32);

        $characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randomStr = ''; 
        for ($i = 0; $i < $num; $i++) { 
            $index = mt_rand(0, strlen($characters) - 1); 
            $randomStr .= $characters[$index]; 
        }


        $timestamp = time();
        //将字符串使用base64加密
        $randomStr = base64_encode($randomStr.$timestamp);

        //替换掉base64加密后面可能产生的==号
        $randomStr = preg_replace("/=/", "", $randomStr);


        $start_num = 0;
        while(true){
            
            //截取指定长度的字符串
            $shortUrlStr = substr($randomStr, $start_num, Config::get("app.short_str_length"));

            

            //如果在redis数据库中未匹配到当前字符串，就说明当前字符串未被使用过，将数据储存在redis中，并且退出当前循环  
            if(!self::getRedisStatus(Config::get("app.redis_prefix").$shortUrlStr)){
                //这个好像没啥用，shortener.php中是$Otherclass->setRedisValue($short_url, $insertItemid)直接覆盖此项代码
                self::setRedisValue(Config::get("app.redis_prefix").$shortUrlStr);  
                break;
            }


            //echo $shortUrlStr."\n";



            $start_num += 1;
            if($start_num > (strlen($randomStr) - Config::get("app.short_url_str_length") - 1)){
                

                //------------    如果所有的数据都匹配完了还是没有匹配到short_str，就将长度+1   begin ------------

                //截取指定长度的字符串
                $shortUrlStr = substr($randomStr, $start_num, Config::get("app.short_url_str_length")+1);

               

                //如果在redis数据库中未匹配到当前字符串，就说明当前字符串未被使用过，将数据储存在redis中，并且退出当前循环
                if(!self::getRedisStatus(Config::get("app.redis_prefix").$shortUrlStr)){
                    self::setRedisValue(Config::get("app.redis_prefix").$shortUrlStr);
                    break;
                }
                
                //------------    如果所有的数据都匹配完了还是没有匹配到short_str，就将长度+1   end ------------

                //如果还是超出指定长度还没有匹配到数据，就将shortUrtStr设置为指定值
                if($start_num > strlen($randomStr) * 2){

                    //截取指定长度的字符串
                    $shortUrlStr = "errorShortStr".$timestamp."-".time();

                    //如果在redis数据库中未匹配到当前字符串，就说明当前字符串未被使用过，将数据储存在redis中，并且退出当前循环
                    if(!self::getRedisStatus(Config::get("app.redis_prefix").$shortUrlStr)){
                        //这个好像没啥用，shortener.php中是$Otherclass->setRedisValue($short_url, $insertItemid)直接覆盖此项代码
                        self::setRedisValue(Config::get("app.redis_prefix").$shortUrlStr);
                        break;
                    }
                }

            }
        }
        






        return $shortUrlStr; 



    }


    public function getHostData($http_host){

        $host_data = Db::query('select * from tp_domain where domain_url="'.$http_host.'" order by itemid asc limit 1;');
        
        return $host_data;
    }




    #expiration time  
    #index
    public static function expiration_index(){
        $expiration_array = [
            "N"    => "Never",
            "10M"  => "10 Minutes",  #60 * 10
            "1H"   => "1 Hour",      #60 * 60
            "1D"   => "1 Day",       #60 * 60 * 24
            "1W"   => "1 Week",      #60 * 60 * 24 * 7
            "2W"   => "2 Weeks",     #60 * 60 * 24 * 14
            "1M"   => "1 Month",     #60 * 60 * 24 * 30
            "6M"   => "6 Months",    #60 * 60 * 24 * 30 * 6
            "1Y"   => "1 Year",      #60 * 60 * 24 * 30 * 12
        ];

        return $expiration_array;
    }


    public static function expiration_key_value(){
        $expiration_array = [
            "N"    => 0,
            "10M"  => 600,      #60 * 10
            "1H"   => 3600,     #60 * 60
            "1D"   => 86400,    #60 * 60 * 24
            "1W"   => 604800,   #60 * 60 * 24 * 7
            "2W"   => 1209600,  #60 * 60 * 24 * 14
            "1M"   => 2592000,  #60 * 60 * 24 * 30
            "6M"   => 15552000, #60 * 60 * 24 * 30 * 6
            "1Y"   => 31104000, #60 * 60 * 24 * 30 * 12
        ];

        return $expiration_array;
    }




    public static function is_spider($user_agent){
        //echo "spider_status";
        if(preg_match("/".Config::get("app.spider_user_agent")."/i", $user_agent)){

            //-------- 屏蔽google蜘蛛 disable google spider end  -------------------

            $spider_status = 1;
        }else{
            $spider_status = 0;
        }
        return $spider_status;
    }

    
    //从shorten程序提取的ip获取工具，能够很准确的获取到IP，只要能获取到用户的IP，可以去掉之前使用用户UA判断的选项
    public static function get_user_ip(){
        if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $ipaddress =  $_SERVER['HTTP_CF_CONNECTING_IP'];
        elseif (isset($_SERVER['HTTP_X_REAL_IP'])) 	 	$ipaddress = $_SERVER['HTTP_X_REAL_IP'];
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))	 		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (isset($_SERVER['HTTP_X_FORWARDED']))		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        elseif (isset($_SERVER['HTTP_FORWARDED']))	$ipaddress = $_SERVER['HTTP_FORWARDED'];
        elseif (isset($_SERVER['REMOTE_ADDR']))	$ipaddress = $_SERVER['REMOTE_ADDR'];
        else $ipaddress = "null";

        return $ipaddress;
    }

    public function userAgent(){
		if(isset($_SERVER["HTTP_USER_AGENT"])) $userAgent= $_SERVER["HTTP_USER_AGENT"];
		elseif(isset($_SERVER["HTTP_REFERER"])) $userAgent= $_SERVER["HTTP_REFERER"];
        else  $userAgent = "None";

        return $userAgent;
	}

    public function device(){
		$platform =   "Unknown OS";
		$os       =  [
					'/windows nt 11.0/i'    =>  'Windows 11',
					'/windows nt 10.0/i'    =>  'Windows 10',
					'/windows nt 6.3/i'     =>  'Windows 8.1',
					'/windows nt 6.2/i'     =>  'Windows 8',
					'/windows nt 6.1/i'     =>  'Windows 7',
					'/windows nt 6.0/i'     =>  'Windows Vista',
					'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
					'/windows nt 5.1/i'     =>  'Windows XP',
					'/windows xp/i'         =>  'Windows XP',
					'/windows nt 5.0/i'     =>  'Windows 2000',
					'/windows me/i'         =>  'Windows ME',
					'/win98/i'              =>  'Windows 98',
					'/win95/i'              =>  'Windows 95',
					'/win16/i'              =>  'Windows 3.11',
					'/macintosh|mac os x/i' =>  'Mac OS X',
					'/mac_powerpc/i'        =>  'Mac OS 9',
					'/linux/i'              =>  'Linux',
					'/ubuntu/i'             =>  'Ubuntu',
					'/iphone/i'             =>  'iPhone',
					'/ipod/i'               =>  'iPod',
					'/ipad/i'               =>  'iPad',
					'/android/i'            =>  'Android',
					'/blackberry/i'         =>  'BlackBerry',
					'/bb10/i'         		=>  'BlackBerry',
					'/cros/i'				=>	'Chrome OS',
					'/webos/i'              =>  'Mobile'
				];
		foreach ($os as $regex => $value) { 
			if (preg_match($regex, $this->userAgent())) {
				$platform    =   $value;
			}
		}   
		return $platform;	
	}


	public function browser() {
		$matched   = 	false;
		$browser   =   "Unknown Browser";
		$browsers  =   [
						'/safari/i'     =>  'Safari',			
						'/firefox/i'    =>  'Firefox',
						'/fxios/i'    	=>  'Firefox',						
						'/msie/i'       =>  'Internet Explorer',
						'/Trident\/7.0/i'  =>  'Internet Explorer',
						'/chrome/i'     =>  'Chrome',
						'/crios/i'		=>	'Chrome',
						'/opera/i'      =>  'Opera',
						'/opr/i'      	=>  'Opera',
						'/netscape/i'   =>  'Netscape',
						'/maxthon/i'    =>  'Maxthon',
						'/konqueror/i'  =>  'Konqueror',
						'/edg/i'       =>  'Edge',
					];
		
		foreach ($browsers as $regex => $value) { 
			if (preg_match($regex,  $this->userAgent())) {
				$browser  =  $value;
				$matched = true;
			}
		}
		
		if(!$matched && preg_match('/mobile/i', $this->userAgent())){
			$browser = 'Mobile Browser';
		}

		return $browser;
	} 


    //返回英文国家名称
    //https://github.com/maxmind/GeoIP2-php#city-example
    public function get_country($ip){
        try {
            $reader = new \GeoIp2\Database\Reader(Config::get("app.install_path").'vendor/geoip2/GeoLite2-City.mmdb');
            $record = $reader->city($ip);
            $country = $record->country->name;

            if(empty($country)){
                $country = "None";
            }
        }catch(\Exception $e){
            $country = "None";
        }

        return $country;
    }
}