<?php
namespace app\admin\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;
use think\facade\View;


class ClickAnalysis extends BaseController 
{   


    protected $middleware = ["app\middleware\CheckLogin::class"];


    public function list(){
        //获取当前url的参数
        $Otherclass = new Otherclass($this->app);

        $list = array();
        for($i=0; $i< Config::get("app.clicks_and_short_url_analysis_days"); $i++){
            $new_array_ = array();
            $date_time = date("Y-m-d", time() - 60*60*24*$i);

            //总点击数量
            $redis_clicks_key = Config::get("app.redis_prefix")."_shortener_clicks_".date("Y-m-d", time() - 60*60*24*$i);
            //pc端点击
            $redis_pc_clicks_key = Config::get("app.redis_prefix")."_shortener_pc_clicks_".date("Y-m-d", time() - 60*60*24*$i);
            //m端点击
            $redis_m_clicks_key = Config::get("app.redis_prefix")."_shortener_m_clicks_".date("Y-m-d", time() - 60*60*24*$i);
            //广告中间页面点击
            $redis_middle_page_clicks_key = Config::get("app.redis_prefix")."_shortener_middle_page_clicks_".date("Y-m-d", time() - 60*60*24*$i);



            $redis_short_url_key = Config::get("app.redis_prefix")."_shortener_short_url_".date("Y-m-d", time() - 60*60*24*$i);

            //获取总点击数
            if($Otherclass->getRedisValue($redis_clicks_key)){
                $total_clicks = $Otherclass->getRedisValue($redis_clicks_key);
            }else{
                $total_clicks = 0;
            }


            //获取pc点击数
            if($Otherclass->getRedisValue($redis_pc_clicks_key)){
                $total_pc_clicks = $Otherclass->getRedisValue($redis_pc_clicks_key);
            }else{
                $total_pc_clicks = 0;
            }


            //获取m端点击数
            if($Otherclass->getRedisValue($redis_m_clicks_key)){
                $total_m_clicks = $Otherclass->getRedisValue($redis_m_clicks_key);
            }else{
                $total_m_clicks = 0;
            }


            //获取中间页点击数
            if($Otherclass->getRedisValue($redis_middle_page_clicks_key)){
                $total_middle_page_clicks = $Otherclass->getRedisValue($redis_middle_page_clicks_key);
            }else{
                $total_middle_page_clicks = 0;
            }

            //获取当日生成的short_url数量
            if($Otherclass->getRedisValue($redis_short_url_key)){
                $total_short_url = $Otherclass->getRedisValue($redis_short_url_key);
            }else{
                $total_short_url = 0;
            }

            //将数据增加到new_array()容器中
            $new_array_["date_time"] = $date_time;
            $new_array_["all_clicks"] = $total_clicks;
            $new_array_["pc_clicks"] = $total_pc_clicks;
            $new_array_["m_clicks"] = $total_m_clicks;
            $new_array_["middle_page_clicks"] = $total_middle_page_clicks;
            $new_array_["short_url"] = $total_short_url;


            array_push($list, $new_array_);
        
            
        }
        //var_dump($list);

        //------------  将读取的数据储存在数据库中 begin ---------------------
        $insert_click_analysis_data = array();

        //将数组使用日期作为键，然后再对其进行升序，这样使用日期插入到数据库中的值就是按照最新日期排序的
        for($i=0; $i < count($list); $i++){
            $insert_click_analysis_data[$list[$i]["date_time"]] = $list[$i];
        }
        
        asort($insert_click_analysis_data);
        foreach($insert_click_analysis_data as $key => $value){
            //当天最新的数据不要插入到数据库中（当天最新的数据值还未积累完）
            if($key != date("Y-m-d", time())){
                $analysis_data = Db::table("tp_click_analysis")->where("date_time","=",$key)->select();
                //如果返回的数据值大于0，则说明数据库中已经有此项数据，否则就将此项数据插入数据库
                if(count($analysis_data) == 0){
                    //var_dump($value);
                    Db::table("tp_click_analysis")->strict(false)->insert($value);
                }
            }
        }
        //------------  将读取的数据储存在数据库中 end ----------------------


        $data_list = Db::table("tp_click_analysis")->order("itemid", "desc")->paginate([
            "list_rows" => Config::get("app.admin_page_num"),
            "path"  => "/".Config::get("app.admin_path")."/clicks_and_shortener_analysis/list"
        ]);

        $today_datetime = date("Y-m-d", time());

        $page_num = Request::param("page") ? Request::param("page") : "1";

        View::assign("data_list",$data_list);
        View::assign("today_datetime",$today_datetime);
        View::assign("page_num",$page_num);
        View::assign("username",Session::get("username"));
        View::assign("list",$list);
        View::assign("pc_url",Config::get("app.admin_url"));
        View::assign("admin_url",Config::get("app.admin_url"));
        View::assign("admin_path",Config::get("app.admin_path"));

        return view::fetch("/ClickAnalysis/list");       

    }



}
