<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (!defined('FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_NAME')) {
    define('FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_NAME', 'user_attribute');
    define('FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_FRONTEND_LABEL', 'frontend_label');
}

/** @var Magento\Customer\Model\Attribute $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);

$model->setName(
    FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_NAME
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
    FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_FRONTEND_LABEL
)->setSortOrder(
    1221
);

$model->save();
