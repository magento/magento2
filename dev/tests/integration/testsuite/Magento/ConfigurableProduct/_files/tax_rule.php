<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$customerTaxClass = $objectManager->create('Magento\Tax\Model\ClassModel')->load('Retail Customer', 'class_name');
$productTaxClass1 = $objectManager->create('Magento\Tax\Model\ClassModel')->load('Taxable Goods', 'class_name');


$taxRate = [
    'tax_country_id' => 'US',
    'tax_region_id' => '0',
    'tax_postcode' => '*',
    'code' => '*',
    'rate' => '10',
];
$rate = $objectManager->create('Magento\Tax\Model\Calculation\Rate')->setData($taxRate)->save();

/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');
$registry->unregister('_fixture/Magento_Tax_Model_Calculation_Rate');
$registry->register('_fixture/Magento_Tax_Model_Calculation_Rate', $rate);

$ruleData = [
    'code' => 'Test Rule',
    'priority' => '0',
    'position' => '0',
    'customer_tax_class_ids' => [$customerTaxClass->getId()],
    'product_tax_class_ids' => [$productTaxClass1->getId()],
    'tax_rate_ids' => [$rate->getId()],
];

$taxRule = $objectManager->create('Magento\Tax\Model\Calculation\Rule')->setData($ruleData)->save();

$registry->unregister('_fixture/Magento_Tax_Model_Calculation_Rule');
$registry->register('_fixture/Magento_Tax_Model_Calculation_Rule', $taxRule);
