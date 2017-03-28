<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * @var $configWriter \Magento\Framework\App\Config\Storage\WriterInterface
 */
$configWriter = $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);

$configWriter->delete(SubscriptionHandler::CRON_STRING_PATH);
$configWriter->save('default/analytics/subscription/enabled', 1);

/**
 * @var $analyticsToken \Magento\Analytics\Model\AnalyticsToken
 */
$analyticsToken = $objectManager->get(\Magento\Analytics\Model\AnalyticsToken::class);
$analyticsToken->storeToken('42');

/**
 * @var $flagManager \Magento\Analytics\Model\FlagManager
 */
$flagManager = $objectManager->get(\Magento\Analytics\Model\FlagManager::class);

$flagManager->deleteFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
