<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Tax\Model\Calculation\RateRepository;
use Magento\Tax\Model\Calculation\Rule;
use Magento\Tax\Model\Calculation\RuleFactory;
use Magento\Tax\Model\TaxRuleRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tax\Model\ResourceModel\Calculation\Rate as RateResource;
use Magento\Tax\Model\ResourceModel\Calculation\Rule as RuleResource;

$objectManager = Bootstrap::getObjectManager();
/** @var RateFactory $rateFactory */
$rateFactory = $objectManager->get(RateFactory::class);
/** @var RuleFactory $ruleFactory */
$ruleFactory = $objectManager->get(RuleFactory::class);
/** @var RateRepository $rateRepository */
$rateRepository = $objectManager->get(TaxRateRepositoryInterface::class);
/** @var TaxRuleRepository $ruleRepository */
$ruleRepository = $objectManager->get(TaxRuleRepositoryInterface::class);
/** @var RateResource $rateResource */
$rateResource = $objectManager->get(RateResource::class);
/** @var RuleResource $ruleResource */
$ruleResource = $objectManager->get(RuleResource::class);

$rate = $rateFactory->create();
$rateResource->load($rate, 'US-TEST-*-Rate-1', Rate::KEY_CODE);
$rule = $ruleFactory->create();
$ruleResource->load($rule, 'GraphQl Test Rule', Rule::KEY_CODE);
$ruleRepository->delete($rule);
$rateRepository->delete($rate);
