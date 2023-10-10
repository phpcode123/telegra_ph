<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [

    // +------------------------------------------------------------------
    // | 网站设置
    // +------------------------------------------------------------------

    'app_name'               => 'Note',
    //admin后台登录的账号用户名和密码
    'admin_username'         => 'admin',    //后台用户名
    'admin_password'         => 'admin123', //后台密码
    'api_password'           => 'a897sad9',


    //后台相关配置项
    'admin_url'              => 'https://your_domain.com/', //后台登录网址
    'admin_path'             => 'admin.php',//后台入口文件，防止后台被爆破
    'admin_page_num'         => '30',//后台分页数量
    'email'                  => '123@gmail.com(#替换@)',
    'install_path'           => '/www/wwwroot/YOUR_DOMAIN_PATH/',


    //服务器升级维护中 ，0为正常,1为维护
    'server_upgrade_status' => 0,
    'server_upgrade_tips'   => 'Server upgrade, please try again after half an hour...',


    //用户在办小时内最大可发布的note数量，避免用户灌水，目前设置是半小时内最大数量为6个
    'max_note_number_in_half_an_hour' => 5,


    //shortener api
    'shortener_url'   => 'https://YOUR_DOMAIN.com/shortener-api',
    'shortener_api_token' => 'a3a205dfe',

    //index template tags display
    'index_template_show_description'  => 0, //首页模板， 1为展示，0为不展示
    'index_template_show_tags'  => 0, //首页模板, 1为展示，0为不展示


    //black_url 注意url中的.要用反斜杠\转义，如：baidu.com应写成baidu\.com (均是使用正则匹配，如果不转义“.”会出现一些奇奇怪怪的问题)
    'black_url_array'      => array(),

    //contact black word 联系方式黑名单关键词，避免恶意灌水,可以匹配邮箱、联系人姓名、联系内容
    'contact_black_word'   => array("rmikhail1985"," Eric ","no-repl"," Eric,","cloedcolela","Helina Aziz"," nісe mаn "," mу sistеr ","website's design","bode-roesch","battletech-newsletter","SEO","henry","forum",".dk","robot","blueliners",".de","money","mailme","mail-online","nіce mаn","pussy","fuck ","href","http","pertitfer","pouring","mаrried","automatic","@o2.pl","Cryto"," href "," rich","progmikhail85"),

    //短链接字符串长度,没有特殊情况，此长度不要随意更改
    'short_str_length'   => 6, //生成短链接字符串的长度
  


    //redis 缓存设置
    'redis_host'             => '127.0.0.1',
    'redis_port'             =>  6379,
    //增加redis的key前缀，防止在同一台服务器上不同的程序之间的redis key重复，造成数据覆盖丢失。
    'redis_prefix'           => 'note-', 





    //用于储存short_url统计数的天数,单位：天， 调用类：Gotourl.php\ shortener.php
    'short_str_hits_analysis_days'  => 10,


    //常见蜘蛛User-agent  Dalvik 是google开发的安卓虚拟机，有大量请求的情况
    //zalo为越南的聊天软件
    'spider_user_agent'      =>  'baiduspider|sogou|google|360spider|YisouSpider|Bytespider|bing|yodao|bot|robot|facebook|meta|twitter|reddit|WhatsApp|tiktok|Dalvik|telegram|crawler|ZaloPC|Zalo|discord',//注意不要以｜结尾，否则会匹配到所有的数据,｜为或运算符
    

    //移动端user_agent 用于程序逻辑判断是否是移动端
    'mobile_user_agent'      =>  'iPhone|Android|ios|mobile',



    
    // //pc和移动端访问就直接跳转
    // //0为直接跳转，1为展示middle page
    'pc_middle_page_switch'  => 0,
    'm_middle_page_switch'   => 0,


    // //跳转中间件展示google adsense广告暂停倒计时时间，单位：秒。设置为0秒就自动跳转,不会展示跳转中介页
    // //注意google adsense后台有一个CTR点击参数（网页点击率），正常不能超过8%，超过8%就可能会封号，跳转中间页等待时间过长会提升CTR值，正常10s左右最好
    // 'middle_page_sleep_time'      => 6, 
    
    // //如果在google_adsense_middle_page_sleep_time时间后用户没有点击页面中的链接，就在..auto_jump_sleeptime自动跳转,此值为0时就不跳转
    // 'middle_page_auto_jump_sleeptime'      => 30, 



    //sitemaps_url_num
    'sitemaps_url_num'             => "20000",





    // ------------------------------------------------------------------
    // 默认跳转页面对应的模板文件【新增】
    'dispatch_success_tmpl' => app()->getRootPath() . '/public/tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'  => app()->getRootPath() . '/public/tpl/dispatch_jump.tpl',

    'http_exception_template'    =>  [
        // 定义404错误的模板文件地址
        404 =>  app()->getRootPath() . '/public/404.html',
        // 还可以定义其它的HTTP status
        401 =>  app()->getRootPath() . '/public/404.html',
    ],

    // ------------------------------------------------------------------
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ["middleware","command"],
    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => 'Page error! Please try again later.',
    // 显示错误信息
    'show_error_msg'   => false,


];
