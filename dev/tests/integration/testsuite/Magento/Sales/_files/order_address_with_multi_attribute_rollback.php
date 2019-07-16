<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;

$attributeCodes = [
    'fixture_address_multiselect_attribute',
    'fixture_address_multiline_attribute',
];
$eavConfigType = 'customer_address';

$objectManager = Bootstrap::getObjectManager();
/** @var OrderAddressRepositoryInterface $salesAddressRepository */
$salesAddressRepository = $objectManager->get(OrderAddressRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
/** @var FilterBuilder $filterBuilder */
$filterBuilder = $objectManager->get(FilterBuilder::class);
$filters = [
    $filterBuilder->setField(OrderAddressInterface::EMAIL)
        ->setValue('multiattribute@example.com')
        ->create(),
];
$searchCriteria = $searchCriteriaBuilder->addFilters($filters)
    ->create();
$saleAddresses = $salesAddressRepository->getList($searchCriteria)
    ->getItems();
foreach ($saleAddresses as $saleAddress) {
    $salesAddressRepository->delete($saleAddress);
}

/** @var AttributeRepositoryInterface $attributerepository */
$attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
/** @var FilterBuilder $filterBuilder */
$filterBuilder = $objectManager->get(FilterBuilder::class);
$filters = [
    $filterBuilder->setField('attribute_code')
        ->setValue(
            [
                'fixture_address_multiline_attribute',
                'fixture_address_multiselect_attribute',
            ]
        )
        ->setConditionType('IN')
        ->create(),
];
$searchCriteria = $searchCriteriaBuilder->addFilters($filters)
    ->create();
$attributes = $attributeRepository->getList($eavConfigType, $searchCriteria)
    ->getItems();
foreach ($attributes as $attribute) {
    $attributeRepository->delete($attribute);
}
