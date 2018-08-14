<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);
$model->setName(
    'address_user_attribute'
)->setEntityTypeId(
    2
)->setAttributeSetId(
    2
)->setAttributeGroupId(
    1
)->setFrontendInput(
    'text'
)->setFrontendLabel(
    'Address user attribute'
)->setIsUserDefined(
    1
);
$model->save();

/** @var \Magento\Customer\Setup\CustomerSetup $setupResource */
$setupResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Setup\CustomerSetup::class
);
$data = [['form_code' => 'customer_address_edit', 'attribute_id' => $model->getAttributeId()]];
$setupResource->getSetup()->getConnection()->insertMultiple(
    $setupResource->getSetup()->getTable('customer_form_attribute'),
    $data
);
