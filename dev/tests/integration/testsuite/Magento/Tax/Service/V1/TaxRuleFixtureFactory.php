<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Service\V1;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * TaxRuleFixtureFactory is meant to help in testing tax by creating/destroying tax rules/classes/rates easily.
 */
class TaxRuleFixtureFactory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
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
        /** @var \Magento\Tax\Service\V1\Data\TaxRuleBuilder $taxRuleBuilder */
        $taxRuleBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        /** @var \Magento\Tax\Service\V1\TaxRuleServiceInterface $taxRuleService */
        $taxRuleService = $this->objectManager->create('Magento\Tax\Service\V1\TaxRuleServiceInterface');

        $rules = [];
        foreach ($rulesData as $ruleData) {
            $taxRuleBuilder->populateWithArray($ruleData);

            $rules[$ruleData['code']] = $taxRuleService->createTaxRule($taxRuleBuilder->create())->getId();
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
        /** @var \Magento\Tax\Service\V1\TaxRuleServiceInterface $taxRuleService */
        $taxRuleService = $this->objectManager->create('Magento\Tax\Service\V1\TaxRuleServiceInterface');

        foreach ($ruleIds as $ruleId) {
            $taxRuleService->deleteTaxRule($ruleId);
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

        /** @var \Magento\Tax\Service\V1\Data\TaxRateBuilder $taxRateBuilder */
        $taxRateBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        /** @var \Magento\Tax\Service\V1\TaxRateServiceInterface $taxRateService */
        $taxRateService = $this->objectManager->create('Magento\Tax\Service\V1\TaxRateServiceInterface');

        $rates = [];
        foreach ($ratesData as $rateData) {
            $code = "{$rateData['country']} - {$rateData['region']} - {$rateData['percentage']}";
            $postcode = '*';
            if (isset($rateData['postcode'])) {
                $postcode = $rateData['postcode'];
                $code = $code . " - " . $postcode;
            }
            $taxRateBuilder->setCountryId($rateData['country'])
                ->setRegionId($rateData['region'])
                ->setPostcode($postcode)
                ->setCode($code)
                ->setPercentageRate($rateData['percentage']);

            $rates[$code] =
                $taxRateService->createTaxRate($taxRateBuilder->create())->getId();
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
        /** @var \Magento\Tax\Service\V1\TaxRateServiceInterface $taxRateService */
        $taxRateService = $this->objectManager->create('Magento\Tax\Service\V1\TaxRateServiceInterface');
        foreach ($rateIds as $rateId) {
            $taxRateService->deleteTaxRate($rateId);
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
