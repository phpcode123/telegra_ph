<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;



class Upload extends BaseController 
{

    public function index()
    {   

       
        $extensions = array("jpg","bmp","gif","png","jpeg","webp");  
        $uploadFilename = $_FILES['file']['name']; 
        $uploadFilesize = $_FILES['file']['size'];
        if($uploadFilesize  > 1024*20*1000){
                echo "<font color=\"red\"size=\"2\">*Over limit ,Image max size 10M.</font>";  
                exit;
        }

        //echo "test";
        $extension = pathInfo($uploadFilename,PATHINFO_EXTENSION);  
        //echo $extension;
        if(in_array($extension,$extensions)){  
            $base_path ="/upload/";
            $date_path = date("Ymd/");
            $uploadPath = $base_path.$date_path;

            if(!is_dir(Config::get("app.install_path")."public".$uploadPath)){
                mkdir(Config::get("app.install_path")."public".$uploadPath,0777,true);
            }
            $file_name = date("Ymd").(int)time().mt_rand(10,99).".".$extension;  
            $desname = Config::get("app.install_path")."public".$uploadPath.$file_name;  
            $image_path = '/upload/'.$date_path.$file_name;  
            $tag = move_uploaded_file($_FILES['file']['tmp_name'],$desname);  
            //$callback = Request::param("CKEditorFuncNum");

            //注意ckeditor返回的数据格式必须是json格式，否则程序不能判断
            $result = array();
            //ckeditor
            // $result["uploaded"] = true;
            // $result["fileName"] = $image_path;
            // $result["url"] = $file_name;
            // echo json_encode($result);

            $result["location"] = $image_path;
            return json_encode($result);
       
            
            

        }else{
            $result = array();
            $result['uploaded'] = false;
            $result['url'] = '';
            $result['message'] = "未知错误！";
            return json_encode($result, true);
        }
    }


}