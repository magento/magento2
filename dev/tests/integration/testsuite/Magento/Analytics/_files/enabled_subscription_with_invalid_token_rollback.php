<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * @var $configWriter WriterInterface
 */
$configWriter = $objectManager->get(WriterInterface::class);
$configWriter->save(SubscriptionHandler::CRON_STRING_PATH, join(' ', SubscriptionHandler::CRON_EXPR_ARRAY));

/**
 * @var $analyticsToken AnalyticsToken
 */
$analyticsToken = $objectManager->get(AnalyticsToken::class);
$analyticsToken->storeToken(null);

/**
 * @var $flagManager FlagManager
 */
$flagManager = $objectManager->get(FlagManager::class);
$flagManager->saveFlag(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, 24);
