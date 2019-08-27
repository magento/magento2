<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);

require __DIR__ . '/../../../Magento/Multishipping/Fixtures/shipping_address_list.php';
require __DIR__ . '/../../../Magento/Multishipping/Fixtures/billing_address.php';
require __DIR__ . '/payment_braintree.php';
require __DIR__ . '/../../../Magento/Multishipping/Fixtures/items.php';
require __DIR__ . '/assign_items_per_address.php';
