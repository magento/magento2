<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$taxRules = [
    'Test Rule',
    'Test Rule Duplicate',
];
$taxClasses = [
    'ProductTaxClass1',
    'ProductTaxClass2',
    'ProductTaxClass3',
    'CustomerTaxClass1',
    'CustomerTaxClass2',
];


$taxRuleResource = $objectManager->get(\Magento\Tax\Model\ResourceModel\Calculation\Rule::class);
foreach ($taxRules as $taxRuleCode) {
    $taxRule = $objectManager->create(\Magento\Tax\Model\Calculation\Rule::class);
    $taxRuleResource->load($taxRule, $taxRuleCode, 'code');
    $taxRuleResource->delete($taxRule);
}

/** @var \Magento\Tax\Model\ResourceModel\TaxClass $resourceModel */
$resourceModel = $objectManager->get(\Magento\Tax\Model\ResourceModel\TaxClass::class);

foreach ($taxClasses as $taxClass) {
    try {
        /** @var \Magento\Tax\Model\ClassModel $taxClassEntity */
        $taxClassEntity = $objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $resourceModel->load($taxClassEntity, $taxClass, 'class_name');
        $resourceModel->delete($taxClassEntity);
    } catch (\Magento\Framework\Exception\CouldNotDeleteException $couldNotDeleteException) {
        // It's okay if the entity already wiped from the database
    }
}
