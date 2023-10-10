<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\View;



class ArticleShow extends BaseController 
{

    public function index(){

        $Otherclass = new Otherclass($this->app);
        $host_data = $Otherclass->getHostData(Request::host());

        if(count($host_data) == 0){
            return "Error,  this domain is not in database, Please try again later.";
        }


        $short_str = Request::param("short_str");


        // Db::table("tp_text")->where("itemid",1)->inc('hits')->update();
        // Db::table("tp_text")->where("itemid",1)->update(['last_visit'=>time()]);

    


        #  ----------------------  tp_http_referer begin ------------------------
        
        $remote_ip = $Otherclass->get_user_ip();
        $country = $Otherclass->get_country($remote_ip);
        $user_agent = $Otherclass->userAgent();


        if($country == "China" && $short_str == "run"){
            abort(404,"The offer is not supported in your Country or Device.");
        }


        $redisValueItemid = $Otherclass->getRedisValue(Config::get("app.redis_prefix").$short_str);
        //echo $short_str;
        //echo $redisValueItemid;
        //更新点击数和最后访问时间戳,点击数增加和最后访问时间以user_agent.short_url来判断，避免客户端刷点击,超时时间半小时  begin
        $hits_add_key = Config::get("app.redis_prefix")."_hits_note_".md5($user_agent)."-".$short_str;
        //echo $hits_add_key;
        if(!$Otherclass->getRedisStatus($hits_add_key)){
            $Otherclass->setRedisValue($hits_add_key, 1, 60*30);


            //更新点击数和最后访问时间
            Db::table("tp_text")->where("itemid",$redisValueItemid)->inc('hits')->update();
            Db::table("tp_text")->where("itemid",$redisValueItemid)->update(['last_visit'=>time()]);

            //---------------------- insert into http_referer begin -------------------------------
            $user_language = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] ? explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] : "none";

        
            $http_referer_data = [
                'short_str'      => $short_str ? $short_str : "null",
                'http_referer'   => Request::server("HTTP_REFERER") ? Request::server("HTTP_REFERER") : "null",
                'user_agent'     => $user_agent,
                'user_language'  => $user_language,
                'remote_ip'      => $remote_ip,
                'country'        => $country,
                'timestamp'      => time()
            ];

            Db::table("tp_http_referer")->strict(false)->insert($http_referer_data);

            //---------------------- insert into http_referer end  -------------------------------
        }
        
        #  ----------------------  tp_http_referer end ------------------------








        #  ------------ 读取数据  begin ----------------------
        if($Otherclass->getRedisValue(Config::get("app.redis_prefix").$short_str)){ 
            $redis_itemid = $Otherclass->getRedisValue(Config::get("app.redis_prefix").$short_str);
            $data = Db::table("tp_text")->where("itemid",$redis_itemid)->select();

            //var_dump($data);

            #如果数据长度为0，就提示错误
            if(count($data) == 0){
                $this->error("URL Error.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/",3);
            }


            //404设置
            if($data[0]['is_404'] == 1){
                abort(404,"is_404 eq 1");
            }


            //1为恶意网址，系脚本自动设置
            if($data[0]['is_malicious'] == 1){
                $this->error("Malicious note!",$host_data[0]['http_prefix'].$host_data[0]['domain_url'].'/',5);
            }







        }else{
            #说明在redis中未读取到数据，直接提示错误
            $this->error("Error URL.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/",3);
        }
        #  ------------ 读取数据  end ----------------------



        #  ------------ content data begin  ----------------------
        $content_data = Db::table("tp_text_content")->where("itemid",$data[0]['itemid'])->select();
        
        if(count($content_data) == 0){
            $content = "Failed to get content, please leave a message and submit error information";
        }else{
            $content = $content_data[0]['content'];
        }


        View::assign("content", $content);
        #  ------------ content data end  ----------------------




        #----------------- 读取cookies Begin ------------------------------------ 
        $cookie_name = Config::get("app.redis_prefix")."_short_str_key_".$short_str;
  
        if(Cookie::has($cookie_name)){
            $cookie_value = Cookie::get($cookie_name);

        }else{
            $cookie_value = 0;
        }
        

        #判断cookies的值（即edit_password）是否与数据库中的一致,用此项来控制show页面的edit button按钮的显示和隐藏
        if((string)$cookie_value == (string)$data[0]['edit_password']){
            $edit_button_show_status = 1;
            //echo " success <br/>";
        }else{
            $edit_button_show_status = 0;
        }


        View::assign("edit_button_show_status", $edit_button_show_status);
        
        #-----------------  读取cookies End  ------------------------------------ 



        $site_name = $host_data[0]['index_title'];
        $template_num = $host_data[0]['template_num']; 
        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";
        
        View::assign("domain_url",$domain_url);



        #  ------------ link_url begin ---------------------
        $link_url = $domain_url.$short_str;
        View::assign("link_url",$link_url);
 
        #  ------------ link_url end ---------------------




        #  ------------ edit url begin ---------------------

        $edit_url = $domain_url."edit/".$short_str;
        View::assign("edit_url",$edit_url);

        #  ------------ eidit url end ---------------------




        






        $title = $data[0]['title'];

        if(empty($title)){
            $title = $host_data[0]['index_title'];
        }


        if($data[0]['tags'] == ""){
            $keywords = $data[0]['title'];
        }else{
            $keywords = $data[0]['title'].",".$data[0]['tags'];
        }
        if($data[0]['description'] == ""){
            $description = $data[0]['title'];
        }else{
            $description = $data[0]['description'];
        }
        

        View::assign("title",$title);
        View::assign("keywords",$keywords);
        View::assign("description",$description);
        View::assign("data",$data);


        

        # ----------------  过期状态判断 begin  -------------------------
        # expiration为0时状态是永久
        $expiration_status = 0;
        
        #如果最后编辑更新时间为0,则根据addtime时间来判断，如果不为0则根据最后更新时间来判断

        if($data[0]['update_time'] == 0){
            if((time() > ($data[0]['addtime'] + $Otherclass->expiration_key_value()[$data[0]['expiration']])) && ($data[0]['expiration'] != "N")){
                $expiration_status = 1;
            }
        }else{
            if((time() > ($data[0]['update_time'] + $Otherclass->expiration_key_value()[$data[0]['expiration']])) && ($data[0]['expiration'] != "N")){
                $expiration_status = 1;
            }
        }

        #模板文件include不支持动态传参，只能使用注册的方式调用模板
        View::assign("template_header", "/template_".$template_num."/header");
        View::assign("template_footer", "/template_".$template_num."/footer");



        if($expiration_status == 1){
            return View::fetch("/template_".$template_num."/Article/expiration");
        }
        # ----------------  过期状态判断 end  -------------------------



        





        
        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        View::assign("domain_data", $domain_data);

        View::assign("domain_url", $domain_url);
        View::assign("site_name", $site_name);
        View::assign("year_num", Config::get("app.year_num"));

     
    
        
        #主要思路是get和post状态，get状态下如果有密码就展示密码输入页，无就正常展示
        #如果有密码就展示密码输入页然后post
        if(Request::isGet()){
            
            
            if($data[0]["visit_password"] != ""){
                return View::fetch("/template_".$template_num."/Article/password");
            }else{

                return View::fetch("/template_".$template_num."/Article/show");
            }
            
        }else{
            $visit_password = Request::param("visit_password") ? Request::param("visit_password") : "";

            #匹配密码
            if($visit_password == $data[0]['visit_password']){



                return View::fetch("/template_".$template_num."/Article/show");
            }else{
                $this->error("Password error! Please try again.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$short_str,10);
            }
            
        }
        
        #  ------------ short_end  begin ----------------------

    
    }

}