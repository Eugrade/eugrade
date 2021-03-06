<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_record.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['record_id']) && !empty($_POST['super']) && !empty($_POST['class_id']) && !empty($_POST['topic_id']) && ($_SESSION['logged_in_id'] == (int) $_POST['super'])) {

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
    $record = (int) input($_POST['record_id']);
    $super = (int) input($_POST['super']);
    $class = (int) input($_POST['class_id']);
    $topic = (int) input($_POST['topic_id']);

    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->andWhere('super', '=', (int) $super)->find();
    if (!$array->super) {
        $status = 0;
        $code = 101;
        $mes = 'Permission denied';
    } else {
        $array = Lazer::table('records')->limit(1)->where('id', '=', (int) $record)->andWhere('belong_topic', '=', (int) $topic)->find()->asArray();
        if (!empty($array)) {
            //删除记录
            Lazer::table('records')->where('id', '=', (int) $record)->andWhere('belong_topic', '=', (int) $topic)->delete();
            //获取当前 topic
            $t = Lazer::table('topics')->limit(1)->where('id', '=', (int) $topic)->find();
            $t->set(array(
                'candidate_count' => $t->candidate_count - 1
            ));
            $t->save();

            $status = 1;
            $code = 106;
            $mes = 'Successfully deleted a record';
        } else {
            $status = 0;
            $code = 105;
            $mes = 'This record does not exist in the topic';
        }
    }
} else {
    $status = 0;
    $code = 103;
    $mes = 'Illegal request';
}

//输出 json
$return = array(
    'status' => $status,
    'code' => $code,
    'mes' => $mes
);
echo json_encode($return);
