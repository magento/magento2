<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
$collection = $objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');
$collection->addAttributeToSelect('id')->load()->delete();

/** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
$collection = $objectManager->create('Magento\Catalog\Model\Resource\Category\Collection');
$collection
    ->addAttributeToFilter('level', 2)
    ->load()
    ->delete();


$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);