<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

$configData = [
    'sendfriend/email/max_per_hour' => 1,
    'sendfriend/email/check_by' => 1,

];
$objectManager = Bootstrap::getObjectManager();
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
foreach ($configData as $key => $value) {
    $defConfig->setDataByPath($key, $value);
    $defConfig->save();
}
