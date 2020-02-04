<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * @var $configWriter \Magento\Framework\App\Config\Storage\WriterInterface
 */
$configWriter = $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);

$configWriter->delete(SubscriptionHandler::CRON_STRING_PATH);
$configWriter->save('analytics/subscription/enabled', 1);

/**
 * @var $analyticsToken \Magento\Analytics\Model\AnalyticsToken
 */
$analyticsToken = $objectManager->get(\Magento\Analytics\Model\AnalyticsToken::class);
$analyticsToken->storeToken('42');

/**
 * @var $flagManager \Magento\Framework\FlagManager
 */
$flagManager = $objectManager->get(\Magento\Framework\FlagManager::class);

$flagManager->deleteFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
