<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\View;


class Redirect extends BaseController 
{
    public function index(){
        $short_str = Request::param("str") ? Request::param("str") : "";
        $url = Request::param("url") ? Request::param("url") : "";


        //-------------   pc和移动端配置自动跳转 begin ---------------
        if(Config::get("app.pc_middle_page_switch") == 0){
            return redirect(urldecode($url),301);
        }

        if(Config::get("app._middle_page_switch") == 0){
            return redirect(urldecode($url),301);
        }
        //-------------   pc和移动端配置自动跳转 end ---------------


        $Otherclass = new Otherclass($this->app);
        $host_data = $Otherclass->getHostData(Request::host());



        # --------------   判断是否是本站来源 begin  --------------  
        if(isset($_SERVER['HTTP_REFERER'])){
            $http_referer = $_SERVER['HTTP_REFERER'];
        }else{
            $http_referer = "";
        }

        if(preg_match("/".$host_data[0]["domain_url"]."/",$http_referer)){
            $http_referer_status = 1;
        }else{
            $http_referer_status = 0;
        }
        View::assign("http_referer_status",$http_referer_status);
        # --------------   判断是否是本站来源 end  --------------   


        #----------------- 首页随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装----------------- 
        #可以有效的去掉非js运行环境的蜘蛛，避免恶意提交数据
        $index_timestamp = time();
        $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
        $Otherclass->setRedisValue(Config::get("app.redis_prefix")."index_hash_str".$hash_str, $value=1, $overtime=60*60*2);
        View::assign("index_timestamp",$index_timestamp);


        #-----------------  首页hash验证 end ----------------- 




        $title = $host_data[0]['index_title'];
        $keywords = $host_data[0]['index_keyword'];
        $description = $host_data[0]['index_description'];
        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";



                #读取cookie
        #----------------- 读取cookies Begin ------------------------------------ 
        $cookie_name = Config::get("app.redis_prefix")."_auto_redirect";



        #如果可以读取到cookie就直接跳转
        if(Cookie::has($cookie_name)){

            //调试时自动跳转就打开此项
            //Cookie::delete($cookie_name);
            return redirect(urldecode($url),301);
        }
   
        #-----------------  读取cookies End  ------------------------------------ 

        $back_to_article_url = $domain_url.$short_str;
        View::assign("back_to_article_url", $back_to_article_url);
        View::assign("url", $url);
        View::assign("encode_url", urlencode($url));
        View::assign("decode_url", urldecode($url));

        View::assign("title", $title);
        View::assign("keywords", $keywords);
        View::assign("description", $description);
        View::assign("domain_url", $domain_url);


        #模板文件include不支持动态传参，只能使用注册的方式调用模板
        $template_num = $host_data[0]['template_num']; 
        View::assign("template_header", "/template_".$template_num."/header");
        View::assign("template_footer", "/template_".$template_num."/footer");
        
        return View::fetch("/template_".$template_num."/Redirect/redirect");
    }


    public function index_post(){
        $skip_checkbox = Request::param("skip_checkbox") ?  Request::param("skip_checkbox") : "0";
        $url = Request::param("url") ?  Request::param("url") : "";



        #----------------- 读取cookies Begin ------------------------------------ 
        $cookie_name = Config::get("app.redis_prefix")."_auto_redirect";
        if($skip_checkbox == 1){
            #cookie过期时间为12小时
            Cookie::set($cookie_name,'1',60*60*12);
            
        }else{
            Cookie::delete($cookie_name);
        }
        #-----------------  读取cookies End  ------------------------------------ 


        
        //echo $skip_checkbox;

        //echo urldecode($url);
        return redirect(urldecode($url),301);
    }

}