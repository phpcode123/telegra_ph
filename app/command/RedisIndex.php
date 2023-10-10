<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\index\controller\Otherclass;
use think\facade\Config;
use think\facade\Db;

class RedisIndex extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\redisindex')->setDescription('the app\command\redisindex command');
    }

    protected function execute(Input $input, Output $output)
    {
        $Otherclass = new Otherclass($this->app);
        Db::table("tp_text")->where("itemid",">","0")->update(['redis_index' => 0]);

        $data = Db::table("tp_text")->where("redis_index","0")->select();

        for($i=0;$i < count($data); $i++){
        
            
            $Otherclass->setRedisValue(Config::get("app.redis_prefix").$data[$i]['short_str'],$data[$i]['itemid']);
            Db::table("tp_text")->where("itemid",$data[$i]['itemid'])->update(["redis_index"=>1]);
            

            echo Config::get("app.redis_prefix").$data[$i]['short_str']." - ".$data[$i]['itemid']."\n";
        }

    }
}
