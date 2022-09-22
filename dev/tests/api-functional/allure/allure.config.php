<?php

require "../../config/AllureConfig.php";

$outputDirectory = __DIR__ . '/../var/allure-results';
return AllureConfig::getAllureConfig($outputDirectory);
