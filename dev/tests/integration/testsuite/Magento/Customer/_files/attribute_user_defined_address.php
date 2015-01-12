<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');
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

/** @var \Magento\Customer\Model\Resource\Setup $setupResource */
$setupResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Model\Resource\Setup',
    ['resourceName' => 'customer_setup']
);
$data = [['form_code' => 'customer_address_edit', 'attribute_id' => $model->getAttributeId()]];
$setupResource->getConnection()->insertMultiple($setupResource->getTable('customer_form_attribute'), $data);
