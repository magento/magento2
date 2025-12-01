<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

require "../../config/AllureConfig.php";

$outputDirectory = __DIR__ . '/../var/allure-results';
return getAllureConfig($outputDirectory);
