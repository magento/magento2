<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Persistent\Model\SessionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var SessionFactory $sessionFactory */
$sessionFactory = $objectManager->get(SessionFactory::class);
$sessionFactory->create()->deleteByCustomerId(1);

require __DIR__ . '/../../Checkout/_files/quote_with_customer_without_address_rollback.php';
