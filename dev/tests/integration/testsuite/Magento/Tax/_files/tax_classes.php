<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$customerTaxClass1 = $objectManager->create(
    'Magento\Tax\Model\ClassModel'
)->setClassName(
    'CustomerTaxClass1'
)->setClassType(
    \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER
)->save();

$customerTaxClass2 = $objectManager->create(
    'Magento\Tax\Model\ClassModel'
)->setClassName(
    'CustomerTaxClass2'
)->setClassType(
    \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER
)->save();

$productTaxClass1 = $objectManager->create(
    'Magento\Tax\Model\ClassModel'
)->setClassName(
    'ProductTaxClass1'
)->setClassType(
    \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
)->save();

$productTaxClass2 = $objectManager->create(
    'Magento\Tax\Model\ClassModel'
)->setClassName(
    'ProductTaxClass2'
)->setClassType(
    \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
)->save();

$taxRate = array(
    'tax_country_id' => 'US',
    'tax_region_id' => '12',
    'tax_postcode' => '*',
    'code' => '*',
    'rate' => '7.5'
);
$rate = $objectManager->create('Magento\Tax\Model\Calculation\Rate')->setData($taxRate)->save();

/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');
$registry->unregister('_fixture/Magento_Tax_Model_Calculation_Rate');
$registry->register('_fixture/Magento_Tax_Model_Calculation_Rate', $rate);

$ruleData = array(
    'code' => 'Test Rule',
    'priority' => '0',
    'position' => '0',
    'tax_customer_class' => array($customerTaxClass1->getId(), $customerTaxClass2->getId()),
    'tax_product_class' => array($productTaxClass1->getId(), $productTaxClass2->getId()),
    'tax_rate' => array($rate->getId())
);

$taxRule = $objectManager->create('Magento\Tax\Model\Calculation\Rule')->setData($ruleData)->save();

$registry->unregister('_fixture/Magento_Tax_Model_Calculation_Rule');
$registry->register('_fixture/Magento_Tax_Model_Calculation_Rule', $taxRule);

$ruleData['code'] = 'Test Rule Duplicate';

$objectManager->create('Magento\Tax\Model\Calculation\Rule')->setData($ruleData)->save();
