<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Debug;
use think\facade\Request;
use think\facade\Config;
use think\facade\View;

class ReportMalicious extends BaseController 
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

            $url = $data["url"];
            $comment = $data["comment"];
            $email = $data["email"];

            $remote_ip = $Otherclass->get_user_ip();
            $user_country = $Otherclass->get_country($remote_ip);


            //验证通过就开始插入数据库
            if($cptc_number_1 + $cptc_number_2 == $cptc){
                $insert_data = [
                    "url" => $url,
                    "remote_ip" => $remote_ip,
                    "country" => $user_country,
                    "comment" => $comment,
                    "email" => $email,
                    "timestamp" => time()
                ];

                if(Db::table("tp_report_malicious_url")->strict(false)->insert($insert_data)){
                    $this->success("Sumbit success.",'/report-malicious',3);
                }else{
                    $this->error("Unknown error",'/report-malicious',3);
                }

            }else{
                $this->error("Captcha error",'/report-malicious',3);
            }

        }else{
            
            $host_data = $Otherclass->getHostData(Request::host());
    
    
            $title = "Report Malicious Text - ".$host_data[0]['site_name'];
            $keywords = "Report Malicious Text";
            $description = "Use the form to report malicious text to our team.";
            
    
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
            
            return View::fetch("/template_".$template_num."/ReportMalicious/report-malicious");
        }



    }
}
