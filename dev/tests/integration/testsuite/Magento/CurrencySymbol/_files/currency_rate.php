<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Directory\Model\Currency $currency */
$currency = $objectManager->create('Magento\Directory\Model\Currency');
$data =     [
    'USD' => [
        'USD' => 1.0000
    ]
];
$currency->saveRates($data);
