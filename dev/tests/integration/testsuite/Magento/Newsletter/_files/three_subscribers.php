<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Newsletter\Model\SubscriberFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Customer/_files/three_customers.php';

$objectManager = Bootstrap::getObjectManager();
$subscriberFactory = $objectManager->get(SubscriberFactory::class);

$subscriberFactory->create()->subscribe('customer@search.example.com');
$subscriberFactory->create()->subscribe('customer2@search.example.com');
$subscriberFactory->create()->subscribe('customer3@search.example.com');
