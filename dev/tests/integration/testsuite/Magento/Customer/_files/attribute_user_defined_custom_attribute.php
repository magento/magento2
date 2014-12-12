<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var Magento\Customer\Model\Attribute $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');

$model->setName(
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
)->setSortOrder(
    1221
);

$model->save();

$model2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');

$model2->setName(
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
);

$model2->save();
