<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$taxRuleResource = $objectManager->get(\Magento\Tax\Model\ResourceModel\Calculation\Rule::class);
$taxRule = $objectManager->create(\Magento\Tax\Model\Calculation\Rule::class);
$taxRuleResource->load($taxRule, 'Test Rule', 'code');
$taxRuleResource->delete($taxRule);

/** @var \Magento\Tax\Model\ResourceModel\TaxClass $resourceModel */
$resourceModel = $objectManager->get(\Magento\Tax\Model\ResourceModel\TaxClass::class);

$customerGroup = $objectManager->create(\Magento\Customer\Model\Group::class)
    ->load('custom_group', 'customer_group_code');
$customerGroup->setTaxClassId(3)->save();

try {
    /** @var \Magento\Tax\Model\ClassModel $taxClassEntity */
    $taxClassEntity = $objectManager->create(\Magento\Tax\Model\ClassModel::class);
    $resourceModel->load($taxClassEntity, 'CustomerTaxClass', 'class_name');
    $resourceModel->delete($taxClassEntity);
} catch (\Magento\Framework\Exception\CouldNotDeleteException $couldNotDeleteException) {
    // It's okay if the entity already wiped from the database
}

/** @var \Magento\Tax\Model\Calculation\Rate $rate */
$rate = $objectManager->get(\Magento\Tax\Model\Calculation\RateFactory::class)->create();
/** @var \Magento\Tax\Model\Calculation\RateRepository $rateRepository */
$rateRepository = $objectManager->get(\Magento\Tax\Model\Calculation\RateRepository::class);
$rate->loadByCode('US-AL-*-Rate-1');
if ($rate->getId()) {
    $rateRepository->delete($rate);
}
