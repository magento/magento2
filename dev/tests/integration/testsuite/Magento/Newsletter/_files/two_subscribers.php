<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Newsletter\Model\SubscriberFactory;

require __DIR__ . '/../../../Magento/Customer/_files/two_customers.php';

$subscriberFactory = $objectManager->get(SubscriberFactory::class);

$subscriberFactory->create()->subscribe('customer@example.com');
$subscriberFactory->create()->subscribe('customer_two@example.com');
