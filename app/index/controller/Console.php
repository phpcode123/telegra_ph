<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Debug;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Cookie;
use GuzzleHttp\Client;
use QL\QueryList;

class Console extends BaseController 
{



    //ads投放广告的点击量，大概可以看出各个国家的点击率和访问量
    //ip数据库必须要准，ip2region国外数据不是很准确，得更换
    public function url_ads(){
        
        $datetime = Request::param("datetime") ? Request::param("datetime") : 1;

        if($datetime == 1){
            echo "Please check parameter datetime.".PHP_EOL;
            return;
        }


        $Otherclass = new Otherclass($this->app);


        $timestamp = strtotime($datetime);
        //echo $timestamp.PHP_EOL;
        $data = Db::table("tp_shortener")->where("timestamp",">",$timestamp)->select();
        $area_data = array();
        for($i=0; $i<count($data); $i++){
            
            //获取ip数据
            $area = $Otherclass->get_country($data[$i]["remote_ip"]).PHP_EOL;
            if(!array_key_exists($area, $area_data)){
                //数据储存格式  area=> url_num,点击数
                $area_data[$area] = "1,".$data[$i]['hits'];
            }else{

                //获取area_data的数组
                $area_data_array = explode(",", $area_data[$area]);

                $area_data_array_url_num = $area_data_array[0]+1;
                $area_data_array_hits = $area_data_array[1]+$data[$i]['hits'];

                //将数据再更新到数组中
                $area_data[$area] = $area_data_array_url_num.",".$area_data_array_hits;

            }
        }


        var_dump($area_data);
        
    }

    //url的访问量数据分析
    public function url_views(){
        
        $datetime = Request::param("datetime") ? Request::param("datetime") : 1;

        if($datetime == 1){
            echo "Please check parameter datetime.".PHP_EOL;
            return;
        }


        $Otherclass = new Otherclass($this->app);


        $timestamp = strtotime($datetime);
        //echo $timestamp.PHP_EOL;
        $data = Db::table("tp_http_referer")->where("timestamp",">",$timestamp)->select();
        $area_data = array();
        for($i=0; $i<count($data); $i++){
            
            //获取ip数据
            $area = $Otherclass->get_country($data[$i]["remote_ip"]).PHP_EOL;
            if(!array_key_exists($area, $area_data)){
                //数据储存格式  area=> url_num,点击数
                $area_data[$area] = "1";
            }else{

                //获取area_data的数组
                $area_data_array = explode(",", $area_data[$area]);

                $area_data_array_visit_num = $area_data_array[0]+1;

                //将数据再更新到数组中
                $area_data[$area] = $area_data_array_visit_num;

            }
        }


        var_dump($area_data);
        
    }

}