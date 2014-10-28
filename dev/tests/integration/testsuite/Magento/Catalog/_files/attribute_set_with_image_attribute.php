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

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create('\Magento\Eav\Model\Entity\Attribute\Set');

$entityType = $objectManager->create('Magento\Eav\Model\Entity\Type')->loadByCode('catalog_product');
$defaultSetId = $objectManager->create('\Magento\Catalog\Model\Product')->getDefaultAttributeSetid();

$data = [
    'attribute_set_name' => 'custom attribute set 531',
    'entity_type_id' => $entityType->getId(),
    'sort_order' => 200,
];

$attributeSet->setData($data);
$attributeSet->validate();
$attributeSet->save();
$attributeSet->initFromSkeleton($defaultSetId);
$attributeSet->save();

$attributeData = array(
    'entity_type_id' => $entityType->getId(),
    'attribute_code' => 'funny_image',
    'frontend_input' => 'media_image',
    'frontend_label' => 'Funny image',
    'backend_type' => 'varchar',
    'is_required' => 0,
    'is_user_defined' => 1,
    'attribute_set_id' => $attributeSet->getId(),
    'attribute_group_id' => $attributeSet->getDefaultGroupId(),
);

/** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
$attribute = $objectManager->create('\Magento\Catalog\Model\Entity\Attribute');
$attribute->setData($attributeData);
$attribute->save();
