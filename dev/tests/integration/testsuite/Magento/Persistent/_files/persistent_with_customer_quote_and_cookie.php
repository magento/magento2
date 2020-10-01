<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Persistent\Model\SessionFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Checkout/_files/quote_with_customer_without_address.php';

$objectManager = Bootstrap::getObjectManager();
/** @var SessionFactory $persistentSessionFactory */
$persistentSessionFactory = $objectManager->get(SessionFactory::class);
$session = $persistentSessionFactory->create();
$session->setCustomerId(1)->save();
$session->setPersistentCookie(10000, '');
