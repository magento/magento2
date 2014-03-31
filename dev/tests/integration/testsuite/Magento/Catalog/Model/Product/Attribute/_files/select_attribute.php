<?php
/**
 * "dropdown" fixture of product EAV attribute.
 *
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

/** @var \Magento\Eav\Model\Entity\Type $entityType */
$entityType = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Eav\Model\Entity\Type');
$entityType->loadByCode('catalog_product');
$defaultSetId = $entityType->getDefaultAttributeSetId();
/** @var \Magento\Eav\Model\Entity\Attribute\Set $defaultSet */
$defaultSet = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Eav\Model\Entity\Attribute\Set'
);
$defaultSet->load($defaultSetId);
$defaultGroupId = $defaultSet->getDefaultGroupId();
$optionData = array('value' => array('option_1' => array(0 => 'Fixture Option')), 'order' => array('option_1' => 1));

/** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Eav\Attribute'
);
$attribute->setAttributeCode(
    'select_attribute'
)->setEntityTypeId(
    $entityType->getEntityTypeId()
)->setAttributeGroupId(
    $defaultGroupId
)->setAttributeSetId(
    $defaultSetId
)->setFrontendInput(
    'select'
)->setFrontendLabel(
    'Select Attribute'
)->setBackendType(
    'int'
)->setIsUserDefined(
    1
)->setOption(
    $optionData
)->save();
