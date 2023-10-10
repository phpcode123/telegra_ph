<?php
namespace app\admin\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;
use think\facade\View;
use think\facade\Cookie;



class Article extends BaseController 
{   


    protected $middleware = ["app\middleware\CheckLogin::class"];

    public function add(){
    
        View::assign("username",Session::get("username"));
        View::assign("admin_path",Config::get("app.admin_path"));
        View::assign("pc_url",Config::get("app.admin_url"));
 


        return view::fetch("/Article/shortener_add");
    }


    public function addpost(){

        $data = Request::post();

        if(Db::table("tp_text")->strict(false)->data($data)->insert()){
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data edit fail",$_SERVER["HTTP_REFERER"],2);
        }
    }


    public function list(){

        $sort = Request::param("sort") ?? "";
        $user_name = Request::param("user_name") ?? "";

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();


        if(empty($sort)){
            if(empty($user_name)){
                $list = Db::table("tp_text")->order("itemid", "desc")->paginate([
                    "list_rows" => Config::get("app.admin_page_num"),
                    "path"  => "/".Config::get("app.admin_path")."/article/list",
                    "query" => Request::param()
                ]);
            }else{
                $list = Db::table("tp_text")->where("user_name",$user_name)->order("itemid", "desc")->paginate([
                    "list_rows" => Config::get("app.admin_page_num"),
                    "path"  => "/".Config::get("app.admin_path")."/article/list",
                    "query" => Request::param()
                ]);
            }
        }else{
            $list = Db::table("tp_text")->order("hits", "desc")->paginate([
                "list_rows" => Config::get("app.admin_page_num"),
                "path"  => "/".Config::get("app.admin_path")."/article/list",
                "query" => Request::only(["sort"])
            ]);
        }
        
        $report_malicious_url = Db::table("tp_report_malicious_url")->where("status","0")->select();
        $contact = Db::table("tp_contact")->where("status","0")->select();


        View::assign("report_malicious_num",count($report_malicious_url));
        View::assign("contact_num",count($contact));

        $last_data = Db::table("tp_text")->order("itemid","desc")->limit(1)->select();
        View::assign("last_data",$last_data);

        View::assign("domain_data",$domain_data);
        View::assign("username",Session::get("username"));
        View::assign("list",$list);
        View::assign("pc_url",Config::get("app.admin_url"));
        View::assign("admin_url",Config::get("app.admin_url"));
        View::assign("admin_path",Config::get("app.admin_path"));

        return view::fetch("/Article/list");       

    }


    public function is_404(){


    

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();

        //var_dump($domain_data);
        $param = Request::param("num") ? Request::param("num") : 0;
        
        if($param != 0){
            $list = Db::table("tp_text")->where("is_404",$param)->order("itemid", "desc")->paginate([
                "list_rows" => Config::get("app.admin_page_num"),
                "path"  => "/".Config::get("app.admin_path")."/article/is_404",
                "query" => Request::only(["num"])
            ]);
        }
        

        //malicious url status 
        $malicious_url_key = Config::get("app.redis_prefix")."malicisou_url_status";
        $malicious_url_status_time = Cache::get($malicious_url_key ,"0");
        //echo $malicious_url_status_time."   ";
        //echo time();
        View::assign("malicious_url_status_time",$malicious_url_status_time);
        View::assign("now_timestamp",time());



        View::assign("domain_data",$domain_data);
        View::assign("username",Session::get("username"));
        View::assign("list",$list);
        View::assign("pc_url",Config::get("app.admin_url"));
        View::assign("admin_url",Config::get("app.admin_url"));
        View::assign("admin_path",Config::get("app.admin_path"));

        return view::fetch("/Article/list");       

    }



    public function edit(){

        $itemid = Request::param("itemid"); 
        $data = Db::table("tp_text")->where("itemid","=",$itemid)->select();

        $content_data = Db::table("tp_text_content")->where("itemid","=",$itemid)->select();

        View::assign("username",Session::get("username"));
        View::assign("data",$data);
        View::assign("content_data",$content_data);
        View::assign("itemid",$itemid);
        View::assign("admin_path",Config::get("app.admin_path"));
        View::assign("pc_url",Config::get("app.admin_url"));
        return view::fetch("/Article/edit");      

    }

    public function editPost(){
        $data = Request::post();
        

        Db::table("tp_text")->strict(false)->where("itemid","=",$data["itemid"])->update($data);

        $content = isset($data["content"]) ? $data["content"] : $data["editor"];

        if(Db::table("tp_text_content")->strict(false)->where("itemid","=",$data["itemid"])->update(["content" => $content])){
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }


       

    }


    public function query(){


        View::assign("username",Session::get("username"));
        View::assign("admin_path",Config::get("app.admin_path"));
        View::assign("pc_url",Config::get("app.admin_url"));
        return view::fetch("/Article/query");
    }

    public function querypost(){
        $short_str = Request::param("short_str") ?? "";

        
        if(empty($short_str)){
            $this->error("short_str is empty.",$_SERVER["HTTP_REFERER"],2);
        }

        //去掉两边的空格
        $short_str = trim($short_str);

        $list = Db::table("tp_text")->where("short_str","=",$short_str)->order("itemid","desc")->paginate([
            "list_rows" => Config::get("app.admin_page_num"),
            "path"     => "/".Config::get("app.admin_path")."/article/querypost",
            "query"    => Request::param(),
        ]);

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        View::assign("domain_data", $domain_data);
        
        View::assign("list",$list);
        View::assign("username",Session::get("username"));
        View::assign("admin_path",Config::get("app.admin_path"));
        View::assign("pc_url",Config::get("app.admin_url"));
        return view::fetch("/Article/query_list");
    }

    public function customize_short_str(){


        View::assign("username",Session::get("username"));
        View::assign("admin_path",Config::get("app.admin_path"));
        View::assign("pc_url",Config::get("app.admin_url"));
        return view::fetch("/Article/customize_short_str");
    }


    public function customize_short_str_post(){

        $itemid = Request::param("itemid") ?? "";
        $short_str = Request::param("short_str") ?? "";

        if(empty($itemid) || empty($short_str)){
            $this->error("Please check your parameter.",$_SERVER["HTTP_REFERER"],3);
        }

        $data = Db::table("tp_text")->where("itemid",$itemid)->select();
        if(count($data) > 0){
            $Otherclass = new Otherclass($this->app);

            //给redis赋值,并更新数据库
            if($Otherclass->setRedisValue(Config::get("app.redis_prefix").$short_str, $itemid)){
                Db::table("tp_text")->where("itemid",$itemid)->update(["short_str" => $short_str]);
                $this->success("success.", $_SERVER["HTTP_REFERER"], 1);
            }else{
                $this->error("Error! Redis set value fail.", $_SERVER["HTTP_REFERER"], 3);
            }

        }else{
            $this->error("itemid error,Data legth is zero.", $_SERVER["HTTP_REFERER"], 3);
        }
        
    }

    public function customize_cookies(){


        View::assign("username",Session::get("username"));
        View::assign("admin_path",Config::get("app.admin_path"));
        View::assign("pc_url",Config::get("app.admin_url"));
        return view::fetch("/Article/customize_cookies");
    }


    public function customize_cookies_post(){

        $itemid = Request::param("itemid") ?? "";

        if(empty($itemid)){
            $this->error("Please check your parameter.",$_SERVER["HTTP_REFERER"],3);
        }

        $data = Db::table("tp_text")->where("itemid",$itemid)->select();
        if(count($data) > 0){
            $Otherclass = new Otherclass($this->app);

            //设置cookies
            #   -------- short_str:edit_password begin -----------
            $cookie_name = Config::get("app.redis_prefix")."_short_str_key_".$data[0]['short_str'];

            Cookie::forever($cookie_name, $data[0]['edit_password']);
            #   -------- short_str:edit_password end -----------


            $this->success("success.", $_SERVER["HTTP_REFERER"], 1);
        }else{
            $this->error("itemid error,Data legth is zero.", $_SERVER["HTTP_REFERER"], 3);
        }
        
    }



    public function delete(){
        $itemid = Request::param("itemid");

        if(empty($itemid)){
            $this->error("itemid is empty.",$_SERVER["HTTP_REFERER"],2);
        }

        if(Db::table("tp_text")->where("itemid",$itemid)->delete()){
            Db::table("tp_text_content")->where("itemid",$itemid)->delete();
            $this->success("Data delete success.","/".Config::get("app.admin_path")."/article/list",1);
        }else{
            $this->error("Data delete fail.","/".Config::get("app.admin_path")."/article/list",2);
        }
    }

}
