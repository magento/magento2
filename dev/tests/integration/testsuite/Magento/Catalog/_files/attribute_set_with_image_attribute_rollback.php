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

/** @var $product \Magento\Catalog\Model\Product */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$entityType = $objectManager->create('Magento\Eav\Model\Entity\Type')->loadByCode('catalog_product');

// remove attribute

/** @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection $attributeCollection */
$attributeCollection = $objectManager->create('\Magento\Catalog\Model\Resource\Product\Attribute\Collection');
$attributeCollection->setFrontendInputTypeFilter('media_image');
$attributeCollection->setCodeFilter('funny_image');
$attributeCollection->setEntityTypeFilter($entityType->getId());
$attributeCollection->setPageSize(1);
$attributeCollection->load();
$attribute = $attributeCollection->fetchItem();
$attribute->delete();

// remove attribute set

/** @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection $attributeSetCollection */
$attributeSetCollection = $objectManager->create('\Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection');
$attributeSetCollection->addFilter('attribute_set_name', 'custom attribute set 531');
$attributeSetCollection->addFilter('entity_type_id', $entityType->getId());
$attributeSetCollection->setOrder('attribute_set_id'); // descending is default value
$attributeSetCollection->setPageSize(1);
$attributeSetCollection->load();

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $attributeSetCollection->fetchItem();
$attributeSet->delete();
