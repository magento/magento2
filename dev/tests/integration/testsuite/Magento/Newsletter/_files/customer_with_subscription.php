<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Newsletter\Model\SubscriberFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/new_customer.php');

$objectManager = Bootstrap::getObjectManager();
$subscriberFactory = $objectManager->get(SubscriberFactory::class);
$subscriberFactory->create()->subscribe('new_customer@example.com');
