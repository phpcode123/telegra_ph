<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'redis_index'  => 'app\command\RedisIndex',
        'delete_text'  => 'app\command\DeleteIllegalText',
    ],
];
