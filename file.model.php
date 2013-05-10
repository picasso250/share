<?php
/**
 * @file    todo
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

require_once 'lib/idiorm.php';

if( defined('SAE_APPNAME') ) {
    ORM::configure('mysql:host='.SAE_MYSQL_HOST_M.';port='.SAE_MYSQL_PORT.';dbname='.SAE_MYSQL_DB);
    ORM::configure('username', SAE_MYSQL_USER);
    ORM::configure('password', SAE_MYSQL_PASS);
} else {
    ORM::configure('mysql:host=localhost;dbname=xc_share');
    ORM::configure('username', 'root');
    ORM::configure('password', '');
}
ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
if (defined('DEV')) {
    ORM::configure('logging', true);
}

function upload_file($file_arr)
{
    $file_name = uniqid().'-'.$file_arr['name'];
    $tmp_file = $file_arr['tmp_name'];
    if (defined('SAE_APPNAME')) {
        $content = file_get_contents($tmp_file);
        $s = new SaeStorage();
        $s->write(UP_DOMAIN , $file_name , $content);
        $uri = $s->getUrl(UP_DOMAIN ,$file_name);
    } else {
        $dir = FILE_ROOT;
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $dir .= '/'.date('Ymd');
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $dest = $dir.'/'.$file_name;
        $rs = move_uploaded_file($tmp_file, $dest);
        if (!$rs) {
            die('upload failed');
        }
        $uri = '/'.substr($dest, strlen(APP_ROOT));
    }
    return save_uri($uri);
}

function save_uri($uri)
{
    $orm = ORM::for_table('file');
    $expire = '1 second';
    $reuse = $orm
        ->where_raw("`created` < DATE_SUB(NOW(), INTERVAL $expire)")
        ->order_by_asc('created')
        ->find_one();
    if ($reuse) {
        if (defined(SAE_APPNAME)) {
            $file_name = preg_match('%http://.+?/(.+)$%', $reuse->uri);
            $s = new SaeStorage();
            $rs = $s->delete(UP_DOMAIN, $file_name);
            if (!$rs) {
                die('delete fail');
            }
        } else {
            $fpath = APP_ROOT.'.'.$uri;
            unlink($fpath);
        }
        $f = $reuse;
    }
    $f = $reuse ?: $orm->create();
    $f->uri = $uri;
    $f->set_expr('created', 'NOW()');
    if (!$f->save()) {
        die('db fail');
    }
    return strtoupper(base_convert($f->id, 10, 36));
}

function get_uri($code)
{
    $f = ORM::for_table('file')->find_one(base_convert(strtolower($code), 36, 10));
    return $f ? $f->uri : null;
}
