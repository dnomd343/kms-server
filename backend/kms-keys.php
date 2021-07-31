<?php

// 数据来源：kms-keys.db
// 请求方式：getKmsKeys($type)
// 返回格式：
// {
//   "version_1": [
//     {
//       "name": 版本名称
//       "key": KMS密钥
//     },
//     ···
//   ],
//   "version_2": [
//     ···
//   ],
//   ···
// }

class kmsDB extends SQLite3 {
    function __construct() {
        $this->open('kms-keys.db'); // KMS密钥数据库
    }
}

function getVersionName($type, $version_id) { // 获取对应版本的名称
    $db = new kmsDB;
    $res = $db->query('SELECT * FROM `' . $type . '_version` WHERE version_id=' . $version_id . ';');
    return  $res->fetchArray(SQLITE3_ASSOC)['version_name'];
}

function getKmsKeys($type) { // 获取所有版本的KMS密钥
    $db = new kmsDB;
    $res = $db->query('SELECT * FROM `' . $type . '`;');
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $index = $row['version'];
        unset($row['version']);
        $data[getVersionName($type, $index)][] = $row;
    }
    return $data;
}

?>