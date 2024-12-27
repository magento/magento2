<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require "../../config/AllureConfig.php";

$outputDirectory = __DIR__ . '/../var/allure-results';
return getAllureConfig($outputDirectory);
