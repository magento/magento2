<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\Data\TaxRuleInterfaceFactory;
use Magento\Tax\Api\Data\TaxRuleInterface;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var TaxClassRepositoryInterface $taxClassRepository */
$taxClassRepository = $objectManager->get(TaxClassRepositoryInterface::class);
$taxClassFactory = $objectManager->get(TaxClassInterfaceFactory::class);
/** @var TaxClassInterface $taxClassDataObject */
$taxClassDataObject = $taxClassFactory->create();
$taxClassDataObject->setClassName('CustomerTaxClass')
    ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
$taxCustomerClassId = $taxClassRepository->save($taxClassDataObject);
$taxClassDataObject = $taxClassFactory->create();
$taxClassDataObject->setClassName('ProductTaxClass')
    ->setClassType(TaxClassManagementInterface::TYPE_PRODUCT);
$taxProductClassId = $taxClassRepository->save($taxClassDataObject);

$taxRateFactory = $objectManager->get(TaxRateInterfaceFactory::class);
/** @var TaxRateInterface $taxRate */
$taxRate = $taxRateFactory->create();
$taxRate->setTaxCountryId('DE')
    ->setTaxRegionId(0)
    ->setTaxPostcode('*')
    ->setCode('Denmark')
    ->setRate('21');
/** @var TaxRateRepositoryInterface $taxRateRepository */
$taxRateRepository = $objectManager->get(TaxRateRepositoryInterface::class);
$taxRate = $taxRateRepository->save($taxRate);

/** @var TaxRuleRepositoryInterface $taxRuleRepository */
$taxRuleRepository = $objectManager->get(TaxRuleRepositoryInterface::class);
$taxRuleFactory = $objectManager->get(TaxRuleInterfaceFactory::class);
/** @var TaxRuleInterface $taxRule */
$taxRule = $taxRuleFactory->create();
$taxRule->setCode('Test Rule')
    ->setCustomerTaxClassIds([$taxCustomerClassId])
    ->setProductTaxClassIds([$taxProductClassId])
    ->setTaxRateIds([$taxRate->getId()])
    ->setPriority(0);
$taxRuleRepository->save($taxRule);
