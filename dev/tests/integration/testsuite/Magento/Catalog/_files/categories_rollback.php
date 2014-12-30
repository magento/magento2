<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var \Magento\Framework\Registry $registry */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
$collection = $objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');
$collection->addAttributeToSelect('id')->load()->delete();

/** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
$collection = $objectManager->create('Magento\Catalog\Model\Resource\Category\Collection');
$collection
    ->addAttributeToFilter('id', ['gt' => 2])
    ->addAttributeToSelect('id')
    ->load()
    ->delete();


$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);