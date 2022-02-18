<?php
$config = [
    'moduleID' => 'a.revo'
];

if (isset($_POST['getModuleID'])) {
    echo json_encode($config['moduleID']);
} else return $config
?>