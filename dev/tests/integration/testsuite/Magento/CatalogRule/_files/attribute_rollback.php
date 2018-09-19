<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Setup\CategorySetup $installer */
$installer = $objectManager->create(\Magento\Catalog\Setup\CategorySetup::class);

/** @var \Magento\Eav\Api\AttributeRepositoryInterface $eavRepository */
$eavRepository = $objectManager->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);

try {
    $attribute = $eavRepository->get($installer->getEntityTypeId('catalog_product'), 'test_attribute');
    $eavRepository->delete($attribute);
} catch (\Exception $ex) {
    //Nothing to remove
}
