<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Customer\Model\Attribute $model1 */
$model1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);
$model1->setName(
    'custom_attribute1'
)->setEntityTypeId(
    2
)->setAttributeSetId(
    2
)->setAttributeGroupId(
    1
)->setFrontendInput(
    'text'
)->setFrontendLabel(
    'custom_attribute_frontend_label'
)->setIsUserDefined(
    1
);
$model1->save();

/** @var \Magento\Customer\Model\Attribute $model2 */
$model2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);
$model2->setName(
    'custom_attribute2'
)->setEntityTypeId(
    2
)->setAttributeSetId(
    2
)->setAttributeGroupId(
    1
)->setFrontendInput(
    'text'
)->setFrontendLabel(
    'custom_attribute_frontend_label'
)->setIsUserDefined(
    1
);
$model2->save();

/** @var \Magento\Customer\Setup\CustomerSetup $setupResource */
$setupResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Setup\CustomerSetup::class
);

$data1 = [['form_code' => 'customer_address_edit', 'attribute_id' => $model1->getAttributeId()]];
$setupResource->getSetup()->getConnection()->insertMultiple(
    $setupResource->getSetup()->getTable('customer_form_attribute'),
    $data1
);

$data2 = [['form_code' => 'customer_address_edit', 'attribute_id' => $model2->getAttributeId()]];
$setupResource->getSetup()->getConnection()->insertMultiple(
    $setupResource->getSetup()->getTable('customer_form_attribute'),
    $data2
);
