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

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');
$model->setName(
    'custom_attribute'
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
    'custom_attributes'
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

/** @var \Magento\Customer\Model\Resource\Setup $setupResource */
$setupResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Model\Resource\Setup',
    array('resourceName' => 'customer_setup')
);

$data = array(array('form_code' => 'customer_address_edit', 'attribute_id' => $model->getAttributeId()));
$setupResource->getConnection()->insertMultiple($setupResource->getTable('customer_form_attribute'), $data);

$data2 = array(array('form_code' => 'customer_address_edit', 'attribute_id' => $model2->getAttributeId()));
$setupResource->getConnection()->insertMultiple($setupResource->getTable('customer_form_attribute'), $data2);
