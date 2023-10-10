<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\View;



class ArticleEdit extends BaseController 
{
    public function edit(){
        $Otherclass = new Otherclass($this->app);
        $host_data = $Otherclass->getHostData(Request::host());

        $short_str = Request::param("short_str") ? Request::param("short_str") : "";

        if(empty($short_str)){
            abort(404, "short_str is empty.");
        }

        $redisValueItemid = $Otherclass->getRedisValue(Config::get("app.redis_prefix").$short_str);
   

        if($redisValueItemid){
            $data = Db::table("tp_text")->where("itemid",$redisValueItemid)->select();
            //如果返回的数据长度为0说明没有获取到数据
            if(count($data) == 0){
                abort(404, "Data length is 0.");
            }
        }else{
            abort(404, "Can not get short_str data in redis.");
        }
        
        //-------------------- 从cookie中获取edit_password begin ---------------
        $edit_password_cookie_name = Config::get("app.redis_prefix")."_short_str_key_".$short_str;
        $edit_password = Cookie::get($edit_password_cookie_name);
        //-------------------- 从cookie中获取edit_password end ---------------

        //echo "123".$edit_password;

        #匹配密码是否正确
        if(empty($edit_password) || $data[0]["edit_password"] != $edit_password){
            $this->error("Permission denied.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$short_str,3);
        }

        


        #----------------- 首页随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装----------------- 
        #可以有效的去掉非js运行环境的蜘蛛，避免恶意提交数据
        $index_timestamp = time();
        $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
        $Otherclass->setRedisValue(Config::get("app.redis_prefix")."index_hash_str".$hash_str, $value=1, $overtime=60*60*2);
        View::assign("index_timestamp",$index_timestamp);


        #-----------------  首页hash验证 end ----------------- 



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





        #  ------------ content data begin  ----------------------
        $content_data = Db::table("tp_text_content")->where("itemid",$data[0]['itemid'])->select();
        
        if(count($content_data) == 0){
            $content = "Fail to get content, please leave a message and submit error information";
        }else{
            $content = $content_data[0]['content'];
        }
        View::assign("content", $content);
        #  ------------ content data end  ----------------------





        #------------- expiration time begin --------------
        $expiration_index = $Otherclass->expiration_index();
        View::assign("expiration_index",$expiration_index);
        #------------- expiration time end --------------


        $template_num = $host_data[0]['template_num']; 
        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";
        
        View::assign("domain_url",$domain_url);



        #模板文件include不支持动态传参，只能使用注册的方式调用模板
        View::assign("template_header", "/template_".$template_num."/header");
        View::assign("template_footer", "/template_".$template_num."/footer");



        $title = "Edit data - ".$data[0]['title'];
        $keywords = $data[0]['title'].",".$data[0]['tags'];
        $description = $data[0]['description'];

        View::assign("title",$title);
        View::assign("keywords",$keywords);
        View::assign("description",$description);
        View::assign("data",$data);


        View::assign("domain_url", $domain_url);
        View::assign("year_num", Config::get("app.year_num"));

    
        return View::fetch("/template_".$template_num."/Article/edit");
            

    
       
    }


    public function edit_post(){
        if(Request::isPost()){
        
            $Otherclass = new Otherclass($this->app);

            $data = Request::param();
            $host_data = $Otherclass->getHostData(Request::host());

            
            #  ------------ short_str begin----------------------
            $short_str = $data['short_str'];
            #  ------------ short_str end----------------------

            #  ---------------------- 首页是否支持JS渲染验证 begin ----------------------
            $hash_str = Request::param("hash_str") ? Request::param("hash_str") : "";

            #如果数据不匹配就提示错误页面
            if(!$Otherclass->getRedisStatus(Config::get("app.redis_prefix")."index_hash_str".$hash_str) || $hash_str == ""){
                $this->error("Please try again.",'/',3);
            }
            #  ---------------------- 首页是否支持JS渲染验证 end ----------------------



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




            # ------------ content begin ----------------------
            $content = isset($data['content']) ? $data['content'] : $data['editor'];

            // echo $content;
            // return;
            
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


            # ------------ description  begin ----------------------
            $description = isset($data['description']) ? $data['description'] : "";
            if(strlen($description) > 258){
                $description = substr($description, 0, 248);
            }
            # ------------ description   end ----------------------


            # ------------ tags begin ----------------------
            $tags = isset($data['tags']) ? $data['tags'] : "";
            if(strlen($tags) > 248){
                $tags = substr($tags, 0, 248);
            }
            # ------------ tags  end ----------------------



            // # ------------ password begin ---------------------
            // $visit_password = $data['visit_password'] ?? "";
            // if(strlen($visit_password) > 200){
            //     $visit_password = "";
            // }
            // # ------------ password end ----------------------



            #------------- expiration time begin --------------
            $expiration = isset($data["expiration"]) ? $data["expiration"] : "N";
            $expiration_array = $Otherclass->expiration_index();
            #判断长度和key是否在数组中
            if(strlen($expiration) > 20 || !array_key_exists($expiration, $expiration_array)){
                $expiration = "N";
            }

            #------------- expiration time end --------------



            # ------------ header  begin ----------------------
            $remote_ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : "none";
            $user_agent = Request::header('User-Agent') ? Request::header('User-Agent') : "none";
            $user_language = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] ? explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] : "none";
            # ------------ header  end ----------------------


            # ------------ is_pc  begin ----------------------
            if(!preg_match("/".Config::get("app.mobile_user_agent")."/i", $user_agent)){
                $is_pc = 1;
            }else{
                $is_pc = 0;
            }
            # ------------ is_pc  end ----------------------
            

            #

            # ------------ insert_data begin ----------------------
            $update_data = [
                "title"     => $title,
                "author"     => $author,
                "description" => $description,
                "tags" => $tags,
                //"visit_password" => $visit_password,
                "update_time" => time(),
                "expiration" => $expiration,
                "is_pc" => $is_pc,
                "remote_ip" => $remote_ip,
                "user_agent" => $user_agent,
                "user_language" => $user_language
            ];

            //var_dump($insert_data);





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
                $cookie_value = $data['short_str'];
            }

            #Save cookie value forever.
            Cookie::forever($cookie_name,$cookie_value);
            #  --------  short_str cookie end ---------------------------
        



            #   -------- short_str:edit_password begin -----------
            $cookie_name_2 = Config::get("app.redis_prefix")."_short_str_key_".$short_str;

            Cookie::forever($cookie_name_2, $data['edit_password']);
            #   -------- short_str:edit_password end -----------

            # -------------------- 储存 cookies end ----------------



            if(strlen($content) < 10){
                $this->error("Note is too short","/",3);
            }else{
                if(Db::table("tp_text")->where("itemid",$data["itemid"])->update($update_data)){


                    #判断插入content数据
                    if(Db::table("tp_text_content")->where("itemid",$data['itemid'])->update(["content" => $content])){

                        $this->success("Successful.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$short_str,1);
                    }else{
                        
                        $this->success("Successful.",$host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$short_str,1);

                    }

                }
            }
        }else{
            abort("404","isGet()");
        }
    }     


}