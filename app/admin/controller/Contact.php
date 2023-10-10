<?php
namespace app\admin\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Debug;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;
use think\facade\View;



class Contact extends BaseController 
{   


    protected $middleware = ["app\middleware\CheckLogin::class"];



    public function list(){
        $status = Request::param("status");

        $list = Db::table("tp_contact")->where("status", "=", $status)->order("itemid", "desc")->paginate([
            "list_rows" => Config::get("app.admin_page_num"),
            "path"  => "/".Config::get("app.admin_path")."/contact/list",
            "query" => Request::only(["status"])
        ]);

        View::assign("username",Session::get("username"));
        View::assign("list",$list);
        View::assign("pc_url",Config::get("app.admin_url"));
        View::assign("admin_url",Config::get("app.admin_url"));
        View::assign("admin_path",Config::get("app.admin_path"));

        return view::fetch("/Contact/contact_list");       

    }


    
    public function read(){
        if(Db::table("tp_contact")->strict(false)->where("status","=",0)->update(["status" => 1])){
            $this->success("Data update success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data update fail.",$_SERVER["HTTP_REFERER"],2);
        }    

    }

    public function edit(){

        $itemid = Request::param("itemid"); 
        $data = Db::table("tp_contact")->where("itemid","=",$itemid)->select();


        View::assign("username",Session::get("username"));
        View::assign("data",$data);
        View::assign("itemid",$itemid);
        View::assign("admin_path",Config::get("app.admin_path"));
        View::assign("pc_url",Config::get("app.admin_url"));
        return view::fetch("/Contact/contact_edit");      

    }

    public function editPost(){
        $itemid = Request::param("itemid");
        $data = Request::post();
    

        

        if(Db::table("tp_contact")->strict(false)->where("itemid","=",$itemid)->update($data)){
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data edit fail.",$_SERVER["HTTP_REFERER"],2);
        }


       

    }

    public function delete(){
        $itemid = Request::param("itemid");


        if(Db::table("tp_contact")->where("itemid",$itemid)->delete()){
            $this->success("Data delete success.","/".Config::get("app.admin_path")."/contact/list",1);
        }else{
            $this->error("Data delete fail.","/".Config::get("app.admin_path")."/contact/list",2);
        }
    }

}
