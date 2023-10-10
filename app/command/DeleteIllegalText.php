<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\index\controller\Otherclass;
use Exception;
use think\facade\Config;
use think\facade\Db;

class DeleteIllegalText extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\deleteillegaltext')
            ->setDescription('the app\command\deleteillegaltext command');
    }

    protected function execute(Input $input, Output $output)
    {   

        try{
            $black_keyword = "porn|zoo|sex|xxx|onlyfans|spanking|cock|adult|erotic|4chan|Black Ass|Xvideo|Xrares|Xnxx|Xhamster|Xev |x77论坛|无码|彩票|中奖号码|人妻|番号|足彩|娱乐城|体彩|吉利论坛|口交|大麻|竞彩|时时彩|赌场|娱乐官网|在线播放|肛交|充气娃娃|龙8国际|麻豆";

            $min_itemid = 217;
            $max_itemid = 19646;
            $text_num = 5000; //每次请求的数据库数量step_num
            $begin_num = 0;
            $step_num = 0;
            while($begin_num  < $max_itemid){
                $step_num += 1;
                $begin_num = $step_num * $text_num;
                if($step_num == 1){
                    $data = Db::table("tp_text")->where('itemid',">",$min_itemid)->where("itemid","<",5000)->select();
                }else{
                    $data = Db::table("tp_text")->where('itemid',">",($step_num-1)*5000)->where("itemid","<",$step_num*5000)->select();
                }

                foreach($data as $item){
                    if(preg_match("#".$black_keyword."#i",$item['title'])){

                        Db::table("tp_text")->where("itemid",$item['itemid'])->delete();
                        Db::table("tp_text_content")->where("itemid",$item['itemid'])->delete();
                        echo ">> ".$item['itemid']." ".$item['title']." Delete success!\n";
                    }
                    
                }
            }

            echo "Running success!\n";
        }catch(\Exception $e){
            dump($e);
        }



    }
}
