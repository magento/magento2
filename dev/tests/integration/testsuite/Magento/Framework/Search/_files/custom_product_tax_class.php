<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var $objectManager \Magento\Framework\ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

/** @var TaxClassInterfaceFactory $taxClassFactory */
$taxClassFactory = $objectManager->get(TaxClassInterfaceFactory::class);

/** @var TaxClassRepositoryInterface $taxClassRepository */
$taxClassRepository = $objectManager->get(TaxClassRepositoryInterface::class);

/** @var TaxClassInterface $taxClass */
$taxClass = $taxClassFactory->create()
    ->setClassType(TaxClassManagementInterface::TYPE_PRODUCT)
    ->setClassName('Test');

$taxClass = $taxClassRepository->save($taxClass);
