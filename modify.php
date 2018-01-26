<?php
require_once ("wp-config.php");
setlocale(LC_ALL, 'ru_RU.UTF-8');

$old_domain = "olddomain.com";
$new_domain = "newdomain.com";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}


$mysqli->query("SET NAMES 'UTF8'");
$mysqli->query("UPDATE " . $table_prefix . "posts SET guid = REPLACE(guid, '" . $old_domain . "', '" . $new_domain . "')");
$mysqli->query("UPDATE " . $table_prefix . "posts SET post_content = REPLACE(post_content, '" . $old_domain . "', '" . $new_domain . "')");
$mysqli->query("UPDATE " . $table_prefix . "postmeta SET meta_value = REPLACE(meta_value, '" . $old_domain . "', '" . $new_domain . "')");

function recursive_array_replace($find, $replace, $array) {
    if (!is_array($array)) {
        return str_replace($find, $replace, $array);
    }
    $newArray = [];
    foreach ($array as $key => $value) {
        $newArray[$key] = recursive_array_replace($find, $replace, $value);
    }
    return $newArray;
}

$result = $mysqli->query("SELECT option_value,option_id FROM " . $table_prefix . "options WHERE option_value LIKE '%" . $old_domain . "%'");
if ($result) {
    while ($r = $result->fetch_assoc()) {
        $z = unserialize($r['option_value']);
        $az = recursive_array_replace($old_domain, $new_domain, $z);
        $f = serialize($az);
        $mysqli->query("UPDATE " . $table_prefix . "options SET option_value = '" . $f . "' WHERE `option_id`='" . $r['option_id'] . "'");
    }
}


echo "all done";
$mysqli->close();
