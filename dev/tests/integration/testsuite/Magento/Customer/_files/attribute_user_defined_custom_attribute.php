<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Magento\Customer\Model\Attribute $attribute1 */
$attribute1 = $objectManager->create(\Magento\Customer\Model\Attribute::class);
$attribute1->setName(
    'custom_attribute1'
)->setEntityTypeId(
    1
)->setIsUserDefined(
    1
)->setAttributeSetId(
    1
)->setAttributeGroupId(
    1
)->setFrontendInput(
    'text'
)->setFrontendLabel(
    'custom_attribute_frontend_label'
)->setBackendType(
    'varchar'
)->setSortOrder(
    1221
)->save();

/** @var Magento\Customer\Model\Attribute $attribute2 */
$attribute2 = $objectManager->create(\Magento\Customer\Model\Attribute::class);
$attribute2->setName(
    'custom_attribute2'
)->setEntityTypeId(
    1
)->setIsUserDefined(
    1
)->setAttributeSetId(
    1
)->setAttributeGroupId(
    1
)->setFrontendInput(
    'text'
)->setFrontendLabel(
    'custom_attributes_frontend_label'
)->setSortOrder(
    1222
)->save();

/** @var Magento\Customer\Model\Attribute $attribute3 */
$attribute3 = $objectManager->create(\Magento\Customer\Model\Attribute::class);
$attribute3->setName(
    'customer_image'
)->setEntityTypeId(
    1
)->setIsUserDefined(
    1
)->setAttributeSetId(
    1
)->setAttributeGroupId(
    1
)->setFrontendInput(
    'image'
)->setFrontendLabel(
    'customer_image'
)->setBackendType(
    'varchar'
)->setSortOrder(
    1223
)->save();
