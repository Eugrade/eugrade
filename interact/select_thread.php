<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_thread.php';

if (!empty($_GET['class_id'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = str_replace("'", "&#39;", $data);
        $data = str_replace('"', "&#34;", $data);
        return $data;
    }

    //获取参数
    $class = input($_GET['class_id']);
    $type = input($_GET['type']);

    if (empty($type)) {
        //业务逻辑
        $array = Lazer::table('classes')->where('id', '=', $class)->findAll()->asArray();
        if (!!$array) {
            $array = Lazer::table('threads')->where('belong_class', '=', (int) $class)->findAll()->asArray();
        } else {
            $array = array(
                'status' => 0,
                'code' => 101,
                'mes' => 'Class not exist'
            );
        }
    } else {
        //业务逻辑
        $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$class)->find()->asArray();
        if (!$array) {
            $array = array(
                'status' => 0,
                'code' => 101,
                'mes' => 'Class not exist'
            );
        }
    }
} else {
    $array = array(
        'status' => 0,
        'code' => 103,
        'mes' => 'Illegal request'
    );
}
echo json_encode($array);
