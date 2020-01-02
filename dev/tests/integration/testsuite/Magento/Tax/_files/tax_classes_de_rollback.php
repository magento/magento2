<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Model\ClassModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Calculation\Rule;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Api\TaxRateRepositoryInterface;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
$taxClasses = [
    'CustomerTaxClass',
    'ProductTaxClass',
];
$taxRuleRepository = $objectManager->get(TaxRuleRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchBuilder */
$searchBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchBuilder->addFilter(Rule::KEY_CODE, 'Test Rule')
    ->create();
$taxRules = $taxRuleRepository->getList($searchCriteria)
    ->getItems();
foreach ($taxRules as $taxRule) {
    try {
        $taxRuleRepository->delete($taxRule);
    } catch (NoSuchEntityException $exception) {
        //Rule already removed
    }
}
$searchCriteria = $searchBuilder->addFilter(ClassModel::KEY_NAME, $taxClasses, 'in')
    ->create();
/** @var TaxClassRepositoryInterface $groupRepository */
$taxClassRepository = $objectManager->get(TaxClassRepositoryInterface::class);
$taxClasses = $taxClassRepository->getList($searchCriteria)
    ->getItems();
foreach ($taxClasses as $taxClass) {
    try {
        $taxClassRepository->delete($taxClass);
    } catch (NoSuchEntityException $exception) {
        //TaxClass already removed
    }
}
$searchCriteria = $searchBuilder->addFilter(Rate::KEY_CODE, 'Denmark')
    ->create();
/** @var TaxRateRepositoryInterface $groupRepository */
$taxRateRepository = $objectManager->get(TaxRateRepositoryInterface::class);
$taxRates = $taxRateRepository->getList($searchCriteria)
    ->getItems();
foreach ($taxRates as $taxRate) {
    try {
        $taxRateRepository->delete($taxRate);
    } catch (NoSuchEntityException $exception) {
        //TaxRate already removed
    }
}
