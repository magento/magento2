<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var $objectManager \Magento\Framework\ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

/** @var TaxClassRepositoryInterface $taxClassRepository */
$taxClassRepository = $objectManager->get(TaxClassRepositoryInterface::class);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter(
    'class_type',
    TaxClassManagementInterface::TYPE_PRODUCT
)
    ->addFilter('class_name', 'Test')
    ->create();

$productTaxClasses = $taxClassRepository->getList($searchCriteria);
$taxClasses = $productTaxClasses->getItems();

/** @var \Psr\Log\LoggerInterface $logger */
$logger = $objectManager->get(\Psr\Log\LoggerInterface::class);

if (!empty($taxClasses)) {
    foreach ($taxClasses as $taxClass) {
        try {
            $taxClassRepository->deleteById($taxClass->getClassId());
        } catch (Exception $e) {
            $logger->critical($e->getMessage(), ['exception' => $e]);
        }
    }
}
