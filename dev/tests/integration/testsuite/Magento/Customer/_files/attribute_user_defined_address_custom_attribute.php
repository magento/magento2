<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');
$model->setName(
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
$model->save();

$model2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');
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
    'Magento\Customer\Setup\CustomerSetup'
);

$data = [['form_code' => 'customer_address_edit', 'attribute_id' => $model->getAttributeId()]];
$setupResource->getSetup()->getConnection()->insertMultiple(
    $setupResource->getSetup()->getTable('customer_form_attribute'),
    $data
);

$data2 = [['form_code' => 'customer_address_edit', 'attribute_id' => $model2->getAttributeId()]];
$setupResource->getSetup()->getConnection()->insertMultiple(
    $setupResource->getSetup()->getTable('customer_form_attribute'),
    $data2
);
