<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\Data\TaxRateTitleInterface;
use Magento\Tax\Api\Data\TaxRuleInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\Rate\Title as RateTitle;
use Magento\Tax\Model\Calculation\Rate\TitleFactory as RateTitleFactory;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Tax\Model\Calculation\RateRepository;
use Magento\Tax\Model\Calculation\Rule;
use Magento\Tax\Model\Calculation\RuleFactory;
use Magento\Tax\Model\TaxRuleRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\DataObjectHelper;

$objectManager = Bootstrap::getObjectManager();
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var RateTitleFactory $rateTitleFactory */
$rateTitleFactory = $objectManager->get(RateTitleFactory::class);
/** @var RateFactory $rateFactory */
$rateFactory = $objectManager->get(RateFactory::class);
/** @var RuleFactory $ruleFactory */
$ruleFactory = $objectManager->get(RuleFactory::class);
/** @var RateRepository $rateRepository */
$rateRepository = $objectManager->get(TaxRateRepositoryInterface::class);
/** @var TaxRuleRepository $ruleRepository */
$ruleRepository = $objectManager->get(TaxRuleRepositoryInterface::class);
/** @var RateTitle */
$rateTitle = $rateTitleFactory->create();
$rateTitleData = [
    RateTitle::KEY_STORE_ID => 1,
    RateTitle::KEY_VALUE_ID => 'Rate Title on storeview 1',
];
/** @var Rate $rate */
$rate = $rateFactory->create();
$rateData = [
    Rate::KEY_COUNTRY_ID => 'US',
    Rate::KEY_REGION_ID => '1',
    Rate::KEY_POSTCODE => '*',
    Rate::KEY_CODE => 'US-TEST-*-Rate-1',
    Rate::KEY_PERCENTAGE_RATE => '7.5',
    Rate::KEY_TITLES => [$rateTitleData]
];
$dataObjectHelper->populateWithArray($rate, $rateData, TaxRateInterface::class);
$rateRepository->save($rate);

$rule = $ruleFactory->create();
$ruleData = [
    Rule::KEY_CODE=> 'GraphQl Test Rule',
    Rule::KEY_PRIORITY => '0',
    Rule::KEY_POSITION => '0',
    Rule::KEY_CUSTOMER_TAX_CLASS_IDS => [3],
    Rule::KEY_PRODUCT_TAX_CLASS_IDS => [2],
    Rule::KEY_TAX_RATE_IDS => [$rate->getId()],
];
$dataObjectHelper->populateWithArray($rule, $ruleData, TaxRuleInterface::class);
$ruleRepository->save($rule);
