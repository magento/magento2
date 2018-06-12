<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require 'default_rollback.php';

/** @var \Magento\Config\Model\Config $defConfig */
$defConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Config\Model\Config::class);
$defConfig->setScope(\Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
$defConfig->setDataByPath('sales_email/general/async_sending', 0);
$defConfig->save();
