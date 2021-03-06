<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

require 'database/db_message.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['user']) && !empty($_POST['file_name']) && !empty($_POST['mes_id']) && !empty($_POST['thread_id']) && !empty($_POST['class_id']) && !empty($_SESSION['logged_in_id'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = str_replace("'","&#39;",$data);
        $data = str_replace('"',"&#34;",$data);
        return $data;
    }

    //获取参数
    $super = input($_POST['user']);
    $mes_id = input($_POST['mes_id']);
    $class = input($_POST['class_id']);
    $filename = $_POST['file_name'];
    $thread = input($_POST['thread_id']);


        //业务逻辑
        $array = Lazer::table('classes')->findAll()->asArray('id');
        if (!array_key_exists($class, $array)) {
            $status = 0;
            $code = 101;
            $mes = 'Class does not exist';
        } else {
            $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->find()->asArray();
            if (!!$array) { //判断操作的用户存在


                if ($array[0]['type'] == 2) { //教师操作
                    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class)->andWhere('super', '=', (int)$super)->find();
                    $speak = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread)->find();
                    if(!!$array->id){ //必须为班级管理员或当前用户才可操作
                        $status = 1;
                    }else{
                        if((int)$speak->speaker !== (int)$super){ //或者是老师本人
                            $status = 0;
                        }else{
                            $status = 1;
                        }
                    }
                } else { //学生操作
                    $array = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread)->find();
                    if((int)$array->speaker !== (int)$super){ //必须为发送者才可操作
                        $status = 0;
                    }elseif($_SESSION['logged_in_id'] == (int)$super){
                        $status = 1;
                    }else{
                        $status = 0;
                    }
                }

                if($status){

                $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->find();
                if (!!$array->name) { //判断主题存在

                    $array = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread)->find();
                        if (!empty($filename) && $array->type == 'file') {

                            $array->set(array(
                                'file_name' => $filename
                            ));
                            $array->save();

                            $status = 1;
                            $code = 133;
                            $mes = 'Successfully edited a message';
                        } else {
                            $status = 0;
                            $code = 120;
                            $mes = 'The FileName can not be empty';
                        }

                } else {
                    $status = 0;
                    $code = 122;
                    $mes = 'The thread does not exist';
                }

            }else{
                $status = 0;
                $code = 114;
                $mes = 'The editor does not match the speaker';
            }
            } else {
                $status = 0;
                $code = 104;
                $mes = 'The speaker does not exist';
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
