<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\View;
use GuzzleHttp\Client;


class Index extends BaseController 
{

    public function index(){
        $Otherclass = new Otherclass($this->app);

        $host_data = $Otherclass->getHostData(Request::host());


        if(count($host_data) == 0){
            return "Error,  Request::host is not in database, Please try again later.";
            
        }


        $title = $host_data[0]['index_title'];
        $keywords = $host_data[0]['index_keyword'];
        $description = $host_data[0]['index_description'];
        $template_num = $host_data[0]['template_num']; 
        
        if(Config::get("app.server_upgrade_status") == 1){
            return  Config::get("app.server_upgrade_tips");
            
        }

        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";


        #读取cookie
        #----------------- 读取cookies Begin ------------------------------------ 
        $cookie_name = Config::get("app.redis_prefix")."_short_str_array";

        if(Cookie::has($cookie_name)){
            $cookie_value = Cookie::get($cookie_name);

        }else{
            $cookie_value = 0;
        }
        
   

        #从数据库中读取数据
        $cookie_data = Db::table("tp_text")->where("short_str","in",$cookie_value)->order("itemid","desc")->limit(15)->select();


        View::assign("cookie_data", $cookie_data);
        
        #-----------------  读取cookies End  ------------------------------------ 



        #----------------- 首页随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装----------------- 
        #可以有效的去掉非js运行环境的蜘蛛，避免恶意提交数据
        $index_timestamp = time();
        $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
        $Otherclass->setRedisValue(Config::get("app.redis_prefix")."index_hash_str".$hash_str, $value=1, $overtime=60*60*2);
        View::assign("index_timestamp",$index_timestamp);


        #-----------------  首页hash验证 end ----------------- 




        # ---------------- expiration time list begin--------------------------
        $expiration_index = $Otherclass->expiration_index();
        View::assign("expiration_index", $expiration_index);
        # ---------------- expiration time list end--------------------------

        
        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        View::assign("domain_data", $domain_data);


        View::assign("domain_url", $domain_url);
        View::assign("title", $title);
        View::assign("keywords", $keywords);
        View::assign("description", $description);
        View::assign("year_num", Config::get("app.year_num"));


        //index template 
        View::assign("index_template_show_description",Config::get("app.index_template_show_description"));
        View::assign("index_template_show_tags",Config::get("app.index_template_show_tags"));



        #模板文件include不支持动态传参，只能使用注册的方式调用模板
        View::assign("template_header", "/template_".$template_num."/header");
        View::assign("template_footer", "/template_".$template_num."/footer");
        
        return View::fetch("/template_".$template_num."/Index/index");

    }


    public function index_post(){

    
        $Otherclass = new Otherclass($this->app);

        $data = Request::param();
        $host_data = $Otherclass->getHostData(Request::host());



        
        #  ---------------------- 首页是否支持JS渲染验证 begin ----------------------
        $hash_str = Request::param("hash_str") ? Request::param("hash_str") : "";

        #如果数据不匹配就提示错误页面
        if(!$Otherclass->getRedisStatus(Config::get("app.redis_prefix")."index_hash_str".$hash_str) || $hash_str == ""){
            $this->error("Please try again.",'/',3);
        }
        #  ---------------------- 首页是否支持JS渲染验证 end ----------------------


        # ------------ header  begin ----------------------
        $remote_ip = $Otherclass->get_user_ip();
        $user_agent = Request::header('User-Agent') ? Request::header('User-Agent') : "none";
        if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
            $user_language = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] ? explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] : "none";
        }else{
            $user_language = "none";
        }# ------------ header  end ----------------------
        


        # ------------ 单用户一小时内最大可发布note的数量，避免用户灌水 ----------------------
        if($Otherclass->getRedisValue(Config::get("app.redis_prefix")."-max_note_number-".$remote_ip)){
            $max_note_number = $Otherclass->getRedisValue(Config::get("app.redis_prefix")."-max_note_number-".$remote_ip);
            if($max_note_number > Config::get("app.max_note_number_in_half_an_hour")){
                $this->error("Over limit, Please try again later.",'/',5);
            }else{
                $Otherclass->setRedisValue(Config::get("app.redis_prefix")."-max_note_number-".$remote_ip, $max_note_number+1, 1800);
            }
        }else{
            $Otherclass->setRedisValue(Config::get("app.redis_prefix")."-max_note_number-".$remote_ip, 1, 1800);
        }
        # ------------ 单用户一小时内最大可发布note的数量，避免用户灌水 ----------------------
        



        # ------------ title begin ----------------------
        $title = $data['title'];
        if(strlen($title) > 248){
            $title = substr($title,0,248);
        }
        # ------------ title end ----------------------

        # ------------ title begin ----------------------
        $author = $data['author'];
        if(strlen($author) > 248){
            $author = substr($author,0,248);
        }
        # ------------ title end ----------------------




        # ------------ description  begin ----------------------
        $description = isset($data['description']) ? $data['description'] : "";
        if(strlen($description) > 248){
            $description = substr($description, 0, 248);
        }
        # ------------ description   end ----------------------


        # ------------ tags begin ----------------------
        $tags = isset($data['tags']) ? $data['tags'] :"";
        if(strlen($tags) > 248){
            $tags = substr($tags, 0, 248);
        }
        # ------------ tags  end ----------------------


        # ------------ timestamp begin ----------------------
        $local_timestamp = isset($data['timestamp']) ? $data['timestamp'] : time();
        if(!is_int($local_timestamp)){
            $local_timestamp = time();
        }
        # ------------ tags  end ----------------------


        # ------------ short_str begin ----------------------
        #$short_str = $data['short_str'];  //自定义字符串暂未开启
        
        #   当short_str为空（客户端未填写）          长度不够时                                长度超长时              使用正则表达式匹配，避免short_str中有非法字符\w+ -字符
        #if($short_str == "" || strlen($short_str) < Config::get("app.short_str_length") || strlen($short_str) > 100 || !preg_match("/^[0-9a-zA-Z-]+$/i",$short_str)){
        #    $short_str = $Otherclass->getShortUrlStr();
        #}

        $short_str = $Otherclass->getShortUrlStr();
        # ------------ short_str end ----------------------

      
        # ------------ content begin ----------------------
        $content = isset($data['content']) ? $data['content'] : $data['editor'];

        
        #开始处理content

        #------------------- 处理链接 beigin -------------------------------
        preg_match_all("/<\s*a.*?href\s*=\s*['\"](.*?)['\"]/i",$content,$content_link);
        #如果未匹配到值返回的数据也是两项，判断array[1]的长度来判断是否有链接
            // array(2) {
            //     [0]=>
            //     array(0) {
            //     }
            //     [1]=>
            //     array(0) {
            //     }
            // }
        //var_dump($content_link);
        //return;

        if(count($content_link[0]) > 0){
            for($i=0;$i<count($content_link[0]); $i++){
                #开始替换链接,如果当前url中未匹配到当前主域的指就替换
                if(!preg_match("#/redirect\?str=#i",$content_link[1][$i])){
                    $content = str_ireplace($content_link[0][$i],"<a href=\"".$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/redirect?str=".$short_str."&amp;url=".urlencode($content_link[1][$i])."\"", $content);
                }
            }
        }


        #------------------- 处理链接 end -------------------------------

        #  常规的标签处理
        $content = preg_replace("/<\s*script[^>]*>([\S\s]*?)<\/.*?script\s*>/i","",$content);
        //$content = preg_replace("/<iframe[^>]*>([\S\s]*?)<\/.*?iframe>/","",$content);  youtube的视频是这个标签
        $content = preg_replace("/<div/i","<dio",$content);
        $content = preg_replace("/<\/div/i","</dio",$content);


        #处理链接
        #给a标签添加rel=“nofollw”; 先替换掉所有的a标签
        $content = preg_replace('/rel\s*=\s*[\'"](.*?)[\'"]/i', '', $content);
        $content = preg_replace('/<a(.*?)>/i', '<a$1 rel="nofollow">', $content);
        
        #给a标签添加rel=“nofollw”; 先替换掉所有的a标签
        $content = preg_replace('/target=[\'"](.*?)[\'"]/i', '', $content);
        $content = preg_replace('/<a(.*?)>/i', '<a$1 target="_blank">', $content);
        
        
        //echo $content;
        //return;
        # ------------ content end  ----------------------
        


        # ------------ password begin ---------------------
        $visit_password = isset($data['visit_password']) ? $data['visit_password'] :"";
        if(strlen($visit_password) > 200){
            $visit_password = "";
        }
        # ------------ password end ----------------------



        #------------- expiration time begin --------------
        $expiration = isset($data["expiration"]) ? $data["expiration"] : "N";

        if(strlen($expiration) > 20){
            $password = "N";
        }

        #------------- expiration time end --------------





        # ------------ edit_password begin ----------------------
        #采用时间戳和随机数字，增加客户端UA，然后取md5,md5长度为32位，起始位置随机0~10，随机截取其中16位

        $edit_password = md5(time().mt_rand(1000,9999)."RAND_STR".$user_agent);
        //$edit_password = substr($edit_password, mt_rand(0,10) ,16);

        # ------------ edit_password  end ----------------------


        # ------------ is_pc  begin ----------------------
        if(!preg_match("/".Config::get("app.mobile_user_agent")."/i", $user_agent)){
            $is_pc = 1;
        }else{
            $is_pc = 0;
        }
        # ------------ is_pc  end ----------------------
        
        
        # ---------------------  使用guzzlehttp远程请求shortener begin ---------------
        /*数据库参数和状态说明
        数据库表项：itemid， token(32位hash字符串)， limit_time(redis限制时间，单位：秒)
            api返回项 
                array = [
                    "code"  => 1,
                    "shorturl" => "",
                    "message"  =>  ""
                ];


            code状态码：
                0  为成功返回shorturl，除了此项其它的项均将shorturl留空
                1  提供的url有问题或者是恶意url
                2  超出速率限制
                3  任何其他的错误包括潜在的问题，比如服务器维护
                4  token有错误
                
        */
        // $short_url = "";
        // try{
        //     $client = new \GuzzleHttp\Client();
        //     $res = $client->request('POST', Config::get("app.shortener_url"), [
        //         'form_params' => [
        //             "url" => $host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$short_str,
        //             "token" => Config::get("app.shortener_api_token")
        //         ]
        //     ]);

        //     $json_array = json_decode($res->getBody(), true);
            
        //     //当状态码为0时说明返回的数据是正确的
        //     if($json_array['code'] == 0){
        //         $short_url= $json_array['shorturl'];
        //     }
        // }catch(\Exception $e){

        // }
        # ---------------------  使用guzzlehttp远程请求shortener end ---------------
        $country = $Otherclass->get_country($remote_ip);

        # ------------ insert_data begin ----------------------
        $insert_data = [
            "short_str" => $short_str,
            "title"     => $title,
            "author"     => $author,
            "description" => $description,
            "tags" => $tags,
            "edit_password" => $edit_password,
            "visit_password" => $visit_password,
            "addtime" => $local_timestamp,
            "expiration" => $expiration,
            "is_pc" => $is_pc,
            "remote_ip" => $remote_ip,
            'country' => $country,
            "user_agent" => $user_agent,
            "user_language" => $user_language
        ];

        //var_dump($insert_data);


        if(strlen($content) < 1){
            $this->error("Note content is too short","/",3);
        }else{
            $insert_itemid = Db::table("tp_text")->strict(false)->insertGetId($insert_data);
            
            #数据插入成功时
            if($insert_itemid){

                # 设置redis short_str => $insert_itemid储存在redis中，一定要先储存redis值，如果此代码在content插入数据后面，content插入完成后会跳转提示，这块代码并没有执行
                if($Otherclass->setRedisValue(Config::get("app.redis_prefix").$short_str, $insert_itemid)){
                    Db::table("tp_text")->where("itemid",$insert_itemid)->update(["redis_index" => 1]);
                }else{
                    $this->error("Error! Please try again later.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/",3);
                }

                #插入数据到content
                $insert_content_data = [
                    "itemid"  => $insert_itemid,
                    "content" => $content,
                ];

                #判断插入content数据
                if(Db::table("tp_text_content")->strict(false)->insert($insert_content_data)){

                
                
                
                
                    # -------------------- 储存 cookies begin ----------------
                    #  --------  short_str cookie begin ----------------------
                    $cookie_name = Config::get("app.redis_prefix")."_short_str_array";

                    if(Cookie::has($cookie_name)){
                        $cookie_value = Cookie::get($cookie_name);

                        #分割字符串并重新组成新的字符串
                        $new_cookie_array = explode(",",$cookie_value);

                        #将short_str添加至数组中
                        array_push($new_cookie_array,$short_str);

                        $cookie_value = "";
                        for($i=0;$i<count($new_cookie_array);$i++){
                            if($i==0){
                                $cookie_value = $new_cookie_array[$i];
                            }else{
                                $cookie_value .= ",".$new_cookie_array[$i];
                            }

                            
                        }

                    }else{
                        $cookie_value = $short_str;
                    }

                    #Save cookie value forever.
                    Cookie::forever($cookie_name,$cookie_value);
                    #  --------  short_str cookie end ---------------------------
                

                    

                    #   -------- short_str:edit_password begin -----------
                    $cookie_name_2 = Config::get("app.redis_prefix")."_short_str_key_".$short_str;

                    Cookie::forever($cookie_name_2, $edit_password);
                    #   -------- short_str:edit_password end -----------

                    # -------------------- 储存 cookies end ----------------


                    
          
                    $this->success("Successful.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$short_str,1);
                }else{
                    #如果失败删除掉数据库指定的itemid数据并提示错误
                    Db::table("tp_text")->where("itemid",$insert_itemid)->delete();
                    $this->error("Error! Please try again.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/",3);

                }

            }
        }
    }     

    
 

}