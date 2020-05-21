<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/**
 * @var StoreManagerInterface $storeManager
 */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$option = [
    'value' => [
        'chair' => ['Chair'],
        'table' => ['Table'],
    ],
    'order' => [
        'chair' => 1,
        'table' => 2,
    ],
];

foreach ($option['value'] as $value => $labels) {
    foreach ($storeManager->getStores() as $store) {
        $labels[$store->getId()] = $labels[0] . '_' . $store->getCode();
    }
    $option['value'][$value] = $labels;
}

require __DIR__ . '/select_attribute.php';
