<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Tax\Api\TaxClassManagementInterface;
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
$taxRuleRepository = $objectManager->get(TaxRuleRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchBuilder */
$searchBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchBuilder->addFilter(Rule::KEY_CODE, 'Test Rule')
    ->create();
$taxRules = $taxRuleRepository->getList($searchCriteria)
    ->getItems();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
foreach ($taxRules as $taxRule) {
    try {
        $taxRuleRepository->delete($taxRule);
    } catch (NoSuchEntityException $exception) {
        //Rule already removed
    }
}

/** @var TaxClassRepositoryInterface $taxClassRepository */
$taxClassRepository = $objectManager->get(TaxClassRepositoryInterface::class);
$searchCriteria = $searchBuilder->addFilter(ClassModel::KEY_NAME, 'CustomerTaxClass')
    ->addFilter(ClassModel::KEY_TYPE, TaxClassManagementInterface::TYPE_CUSTOMER)
    ->create();
$taxClasses = $taxClassRepository->getList($searchCriteria)->getItems();
$searchCriteria = $searchBuilder->addFilter(ClassModel::KEY_NAME, 'ProductTaxClass')
    ->addFilter(ClassModel::KEY_TYPE, TaxClassManagementInterface::TYPE_PRODUCT)
    ->create();
$taxClasses = array_merge($taxClasses, $taxClassRepository->getList($searchCriteria)->getItems());
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
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
