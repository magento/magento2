<?php

require __DIR__ . '/app/bootstrap.php';

$data = [

    "test" => "123",
    "number" => 12
];

echo \Zend_Json::encode($data);

echo \Magento\Zend\Model\Json::encode($data);