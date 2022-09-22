<?php

require_once __DIR__ . "/../../config/AllureConfig.php";

$outputDirectory = __DIR__ . '/../var/allure-results';
return AllureConfig::getAllureConfig($outputDirectory);
