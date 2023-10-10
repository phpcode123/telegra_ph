<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\View;



class ArticleCount extends BaseController 
{

    public function views(){

        $Otherclass = new Otherclass($this->app);
        $user_agent = Request::header('user-agent') ? Request::header('user-agent') : "";
        $user_accept = Request::header('accept') ? Request::header('accept')  : "";
        $user_hash = md5($user_agent.$user_accept);
        
        $itemid = Request::param("itemid") ? Request::param("itemid") : "";
        $short_str = Request::param("short_str")? Request::param("short_str") :"";



        $isSpider = $Otherclass->is_spider($user_agent);



        if($itemid == "" || $short_str == "" || $isSpider == 1){
            abort("404","error");
        }


        $data = Db::table("tp_text")->field("itemid,short_str,hits")->where("itemid",$itemid)->select();

        if((count($data) > 0) && ($data[0]['short_str'] == $short_str)){



            #  ------------ 读取redis数据  begin ----------------------
            $redis_key = Config::get("app.redis_prefix")."_count_hits_".$itemid."_".$short_str."_".$user_hash;
            if(!$Otherclass->getRedisValue($redis_key)){ 

                Db::table("tp_text")->where("itemid",$itemid)->inc('hits')->update();
                Db::table("tp_text")->where("itemid",$itemid)->update(['last_visit'=>time()]);



                //---------------------- insert into http_referer begin -------------------------------
                $user_language = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] ? explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"])[0] : "none";


                $http_referer_data = [
                    'short_str'      => $short_str ? $short_str : "null",
                    'http_referer'   => Request::server("HTTP_REFERER") ? Request::server("HTTP_REFERER") : "null",
                    'user_agent'     => $user_agent,
                    'user_language'  => $user_language,
                    'remote_ip'      => Request::server("REMOTE_ADDR") ? Request::server("REMOTE_ADDR") : "null",
                    'timestamp'      => time()
                ];

                Db::table("tp_http_referer")->strict(false)->insert($http_referer_data);

                //---------------------- insert into http_referer end -------------------------------

                
                $json_array = [
                    "status" =>1 ,
                    "data" => ($data[0]['hits']+1)
                ];
                echo json_encode($json_array);

                #超时时间设置为6小时
                $Otherclass->setRedisValue($redis_key, "1", 60*60*6);
            }else{
                $json_array = [
                    "status" => 0,
                    "data" => "Already voted."
                ];
                echo json_encode($json_array);
            }
            #  ------------ 读取redis数据  end ----------------------
        }else{
            abort("404","error");
        }

    }


    public function positive(){

        $Otherclass = new Otherclass($this->app);
        $user_agent = Request::header('user-agent') ? Request::header('user-agent') : "";
        $user_accept = Request::header('accept') ? Request::header('accept')  : "";
        $user_hash = md5($user_agent.$user_accept);
        

        $vote_str = Request::param("vote_str") ? Request::param("vote_str") :"";
        $itemid = Request::param("itemid") ? Request::param("itemid") : "";
        $short_str = Request::param("short_str") ? Request::param("short_str") :"";
        

        $isSpider = $Otherclass->is_spider($user_agent);

        if($itemid == "" || $short_str == "" || $vote="" || $isSpider == 1){
            abort("404","error");
        }

        $data = Db::table("tp_text")->field("itemid,short_str,positive,negative")->where("itemid",$itemid)->select();


        #  ------------ 读取redis数据  begin ----------------------
        $redis_key = Config::get("app.redis_prefix")."_count_vote_".$itemid."_".$short_str."".$user_hash;
        if(!$Otherclass->getRedisValue($redis_key) && $data[0]['short_str'] == $short_str){ 

            Db::table("tp_text")->where("itemid",$itemid)->inc("positive")->update();
            

            $json_array = [
                "status" =>1 ,
                "data" => ($data[0]["positive"]+1),
                "tips" => "Voting succeeded."
            ];

            echo json_encode($json_array);

            #超时时间设置为6小时
            $Otherclass->setRedisValue($redis_key, "1", 60*60*6);
        }else{
            $json_array = [
                "status" => 0,
                "data" => $data[0]["positive"],
                "tips" => "Already voted."
            ];
            echo json_encode($json_array);
        }
        #  ------------ 读取redis数据  end ----------------------
    }

    public function negative(){

        $Otherclass = new Otherclass($this->app);
        $user_agent = Request::header('user-agent') ? Request::header('user-agent') : "";
        $user_accept = Request::header('accept') ? Request::header('accept')  : "";
        $user_hash = md5($user_agent.$user_accept);
        


        $itemid = Request::param("itemid") ? Request::param("itemid") : "";
        $short_str = Request::param("short_str") ? Request::param("short_str") :"";
        

        $isSpider = $Otherclass->is_spider($user_agent);

        if($itemid == "" || $short_str == "" || $vote="" || $isSpider == 1){
            abort("404","error");
        }

        $data = Db::table("tp_text")->field("itemid,short_str,positive,negative")->where("itemid",$itemid)->select();


        #  ------------ 读取redis数据  begin ----------------------
        $redis_key = Config::get("app.redis_prefix")."_count_vote_".$itemid."_".$short_str."".$user_hash;
        if(!$Otherclass->getRedisValue($redis_key) && $data[0]['short_str'] == $short_str){ 

            Db::table("tp_text")->where("itemid",$itemid)->inc("negative")->update();
            

            $json_array = [
                "status" =>1 ,
                "data" => ($data[0]["negative"]+1),
                "tips" => "Voting succeeded."
            ];

            echo json_encode($json_array);

            #超时时间设置为6小时
            $Otherclass->setRedisValue($redis_key, "1", 60*60*6);
        }else{
            $json_array = [
                "status" => 0,
                "data" => $data[0]["negative"],
                "tips" => "Already voted."
            ];
            echo json_encode($json_array);
        }
        #  ------------ 读取redis数据  end ----------------------
    }
}