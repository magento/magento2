<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Tax\Model\Calculation\RateRepository;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory;
use Magento\Tax\Model\TaxRuleRepository;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var TaxRuleRepository $taxRuleRepository */
$taxRuleRepository = $objectManager->get(TaxRuleRepository::class);
/** @var Collection $taxRuleCollection */
$taxRuleCollection = $objectManager->get(CollectionFactory::class)->create();
/** @var Rate $rate */
$rate = $objectManager->get(RateFactory::class)->create();
/** @var RateRepository $rateRepository */
$rateRepository = $objectManager->get(RateRepository::class);
$taxRuleCollection->addFieldToFilter('code', '36104 Test Rule');
$taxRule = $taxRuleCollection->getFirstItem();
if ($taxRule->getId()) {
    $taxRuleRepository->delete($taxRule);
}

$rate->loadByCode('US-AL-*-Rate-1');
if ($rate->getId()) {
    $rateRepository->delete($rate);
}
