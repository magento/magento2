<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Persistent\Model\SessionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var SessionFactory $sessionFactory */
$sessionFactory = $objectManager->get(SessionFactory::class);
$sessionFactory->create()->deleteByCustomerId(1);

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_customer_without_address_rollback.php');
