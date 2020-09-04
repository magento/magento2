<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Newsletter\Model\SubscriberFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/unconfirmed_customer.php');

$objectManager = Bootstrap::getObjectManager();
/** @var SubscriberFactory $subscriberFactory */
$subscriberFactory = $objectManager->get(SubscriberFactory::class);
$subscriberFactory->create()->subscribe('unconfirmedcustomer@example.com');
