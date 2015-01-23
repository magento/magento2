<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\CurrencySymbol\Model\System\Currencysymbol $currencySymbol */
$currencySymbol = $objectManager->create('Magento\CurrencySymbol\Model\System\Currencysymbol');

$currencySymbol->setCurrencySymbolsData(
    [
        'USD' => 'U',
        'EUR' => 'E',
    ]
);
