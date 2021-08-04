<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$attributeCode = 'street';
$entityType = \Magento\Customer\Model\Metadata\AddressMetadata::ENTITY_TYPE_ADDRESS;
//@codingStandardsIgnoreFile
/** @var \Magento\Customer\Model\Attribute $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);
$model->loadByCode($entityType, $attributeCode);
$validationRules = $model->getValidationRules();

if(!empty($validationRules['input_validation'])){
    if(in_array('alphanum-with-spaces', $validationRules)){
        unset($validationRules['input_validation']);
        $model->setValidationRules($validationRules);
        $model->save();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
