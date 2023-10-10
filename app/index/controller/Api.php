<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\View;
use GuzzleHttp\Client;


class Api extends BaseController 
{


    public function index(){



        $api_password = Request::param("api_password") ?? Request::param("api_password");

        if(Request::isPost() && Config::get("app.api_password") == $api_password){


        
            $Otherclass = new Otherclass($this->app);

            $data = Request::param();
            $host_data = $Otherclass->getHostData(Request::host());



            # ------------ header  begin ----------------------
            $remote_ip = $Otherclass->get_user_ip();
            $user_agent = Request::header('User-Agent') ? Request::header('User-Agent') : "none";
            if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
                $user_language = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] ? explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] : "none";
            }else{
                $user_language = "FORM_API";
            }
            # ------------ header  end ----------------------
            


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


            # ------------ hits begin ----------------------
            $hits = $data['hits'];
            if(!is_int($hits)){
                $hits = mt_rand(0,10000);
            }
            # ------------ hits end ----------------------


            # ------------ timestamp begin ----------------------
            $local_timestamp = isset($data['timestamp']) ? $data['timestamp'] : time();
            if(!is_int($local_timestamp)){
                $local_timestamp = time();
            }

            try{
                //将已存在的时间格式转化为时间戳，如：2022-03-11T21:28:53+0000    输出：1647034133
                if($data['timestamp_switch'] == 1){
                    $local_timestamp = strtotime($data['timestamp']);
                }
            }catch(\Exception $e){
                //do something
            }


            //随机生成时间戳,时间戳随机提前10-300天的时间   86400=60*60*24
            if($data['timestamp_rand'] == 1){
                $rand_num = mt_rand(10,300);
                $local_timestamp = time() - ($rand_num * 86400);
            
               // echo time()."-".(mt_rand(10,300)*86400)." - ".$rand_num;
               // echo "123 -- ".$local_timestamp;
            }
            //echo $local_timestamp;
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
                "hits" => $hits,
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

            
            
                        echo "Insert data success!";
                    }else{
                        #如果失败删除掉数据库指定的itemid数据并提示错误
                        Db::table("tp_text")->where("itemid",$insert_itemid)->delete();
                        echo "Insert data failed!";

                    }

                }
            }
        }else{
            echo "Api password error.";
        }
    }     

    
 

}