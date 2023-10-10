<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\View;

class Contact extends BaseController 
{

    public function index(){
        $Otherclass = new Otherclass($this->app);

        //当请求模式为post时
        if(Request::isPost()){

            $data = Request::post();

            //数字验证码
            $cptc = $data["cptc"];
            $cptc_number_1 = $data["cptc_number_1"];
            $cptc_number_2 = $data["cptc_number_2"];

            $name = $data["name"];
            $message = $data["message"];
            $email = $data["email"];


            //--------------------黑名单关键词判断 begin-------------------------
            $black_num = 0;//黑名单状态码
            $contact_black_word = Config::get("app.contact_black_word");
            for($x=0;$x<count($contact_black_word);$x++){
                if(preg_match_all('/'.$contact_black_word[$x].'/i',$message) || preg_match_all('/'.$contact_black_word[$x].'/i',$name)  || preg_match_all('/'.$contact_black_word[$x].'/i',$email)){
                    
                    $black_num = 1;
                    break;//跳出当前循环
                }
            }


            if($black_num == 1){
                
                $this->error("Error, Please try again!",'/',10);
            }

            //--------------------黑名单关键词判断 end -------------------------

            $remote_ip = $Otherclass->get_user_ip();
            $user_country = $Otherclass->get_country($remote_ip);



            //验证通过就开始插入数据库
            if($cptc_number_1 + $cptc_number_2 == $cptc){
                $insert_data = [
                    "name" => $name,
                    "remote_ip" => $remote_ip,
                    "country" => $user_country,
                    "message" => $message,
                    "email" => $email,
                    "timestamp" => time()
                ];

                if(Db::table("tp_contact")->strict(false)->insert($insert_data)){
                    $this->success("Submit success.",'/contact',5);
                }else{
                    $this->error("Unknown error",'/contact',5);
                }

            }else{
                $this->error("Captcha error",'/contact',3);
            }

        }else{
            
            $host_data = $Otherclass->getHostData(Request::host());
    
    
            $title = "Contact - ".$host_data[0]['site_name'];
            $keywords = "contact";
            $description = "If you have a question or a problem, you can reach our team by using the contact form.";
            
    
            $domain_url = $host_data[0]['http_prefix'].Request::host()."/";
    
    
            $cptc_number_1 = mt_rand(0,9);
            $cptc_number_2 = mt_rand(0,9);
            View::assign("cptc_number_1",$cptc_number_1);
            View::assign("cptc_number_2",$cptc_number_2);


            View::assign("domain_url", $domain_url);
            View::assign("title", $title);
            View::assign("keywords", $keywords);
            View::assign("description", $description);
            

            #模板文件include不支持动态传参，只能使用注册的方式调用模板
            $template_num = $host_data[0]['template_num'];
            View::assign("template_header", "/template_".$template_num."/header");
            View::assign("template_footer", "/template_".$template_num."/footer");
            
            return View::fetch("/template_".$template_num."/Contact/contact");
        }



    }
}
