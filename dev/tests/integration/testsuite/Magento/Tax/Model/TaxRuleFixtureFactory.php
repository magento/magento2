<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * TaxRuleFixtureFactory is meant to help in testing tax by creating/destroying tax rules/classes/rates easily.
 */
class TaxRuleFixtureFactory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Helper to create tax rules.
     *
     * @param array $rulesData Keys match TaxRuleBuilder populateWithArray
     * @return array code => rule id
     */
    public function createTaxRules($rulesData)
    {
        /** @var \Magento\Tax\Api\Data\TaxRuleDataBuilder $taxRuleBuilder */
        $taxRuleBuilder = $this->objectManager->create('Magento\Tax\Api\Data\TaxRuleDataBuilder');
        /** @var \Magento\Tax\Api\TaxRuleRepositoryInterface $taxRuleService */
        $taxRuleService = $this->objectManager->create('Magento\Tax\Api\TaxRuleRepositoryInterface');

        $rules = [];
        foreach ($rulesData as $ruleData) {
            $taxRuleBuilder->populateWithArray($ruleData);

            $rules[$ruleData['code']] = $taxRuleService->save($taxRuleBuilder->create())->getId();
        }

        return $rules;
    }

    /**
     * Helper function that deletes tax rules
     *
     * @param int[] $ruleIds
     */
    public function deleteTaxRules($ruleIds)
    {
        /** @var \Magento\Tax\Api\TaxRuleRepositoryInterface $taxRuleService */
        $taxRuleService = $this->objectManager->create('Magento\Tax\Api\TaxRuleRepositoryInterface');

        foreach ($ruleIds as $ruleId) {
            $taxRuleService->deleteById($ruleId);
        }
    }

    /**
     * Helper function that creates rates based on a set of input percentages.
     *
     * Returns a map of percentage => rate
     *
     * @param array $ratesData array of rate data, keys are 'country', 'region' and 'percentage'
     * @return int[] Tax Rate Id
     */
    public function createTaxRates($ratesData)
    {
        /** @var \Magento\Tax\Api\Data\TaxRateDataBuilder $taxRateBuilder */
        $taxRateBuilder = $this->objectManager->create('Magento\Tax\Api\Data\TaxRateDataBuilder');
        /** @var \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateService */
        $taxRateService = $this->objectManager->create('Magento\Tax\Api\TaxRateRepositoryInterface');

        $rates = [];
        foreach ($ratesData as $rateData) {
            $code = "{$rateData['country']} - {$rateData['region']} - {$rateData['percentage']}";
            $postcode = '*';
            if (isset($rateData['postcode'])) {
                $postcode = $rateData['postcode'];
                $code = $code . " - " . $postcode;
            }
            $taxRateBuilder->setTaxCountryId($rateData['country'])
                ->setTaxRegionId($rateData['region'])
                ->setTaxPostcode($postcode)
                ->setCode($code)
                ->setRate($rateData['percentage']);

            $rates[$code] =
                $taxRateService->save($taxRateBuilder->create())->getId();
        }
        return $rates;
    }

    /**
     * Helper function that deletes tax rates
     *
     * @param int[] $rateIds
     */
    public function deleteTaxRates($rateIds)
    {
        /** @var \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateService */
        $taxRateService = $this->objectManager->create('Magento\Tax\Api\TaxRateRepositoryInterface');
        foreach ($rateIds as $rateId) {
            $taxRateService->deleteById($rateId);
        }
    }

    /**
     * Helper function that creates tax classes based on input.
     *
     * @param array $classesData Keys include 'name' and 'type'
     * @return array ClassName => ClassId
     */
    public function createTaxClasses($classesData)
    {
        $classes = [];
        foreach ($classesData as $classData) {
            /** @var \Magento\Tax\Model\ClassModel $class */
            $class = $this->objectManager->create('Magento\Tax\Model\ClassModel')
                ->setClassName($classData['name'])
                ->setClassType($classData['type'])
                ->save();
            $classes[$classData['name']] = $class->getId();
        }
        return $classes;
    }

    /**
     * Helper function that deletes tax classes
     *
     * @param int[] $classIds
     */
    public function deleteTaxClasses($classIds)
    {
        /** @var \Magento\Tax\Model\ClassModel $class */
        $class = $this->objectManager->create('Magento\Tax\Model\ClassModel');
        foreach ($classIds as $classId) {
            $class->load($classId);
            $class->delete();
        }
    }
}
