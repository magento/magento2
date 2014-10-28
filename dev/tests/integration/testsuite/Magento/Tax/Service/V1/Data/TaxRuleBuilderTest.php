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
namespace Magento\Tax\Service\V1\Data;

/**
 * Integration test for \Magento\Tax\Service\V1\Data\TaxRuleBuilder
 */
class TaxRuleBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * TaxRule builder
     *
     * @var TaxRuleBuilder
     */
    private $builder;

    /**
     * TaxRate builder
     *
     * @var TaxRateBuilder
     */
    private $taxRateBuilder;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->builder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $this->taxRateBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRateBuilder');
    }

    /**
     * @param array $dataArray Array with data for TaxRule
     * @dataProvider createDataProvider
     */
    public function testPopulateWithArray($dataArray)
    {
        $taxRuleFromPopulate = $this->builder->populateWithArray($dataArray)->create();
        $taxRuleFromSetters = $this->generateTaxRuleWithSetters($dataArray);
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRule', $taxRuleFromPopulate);
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRule', $taxRuleFromSetters);
        $this->assertEquals($taxRuleFromSetters, $taxRuleFromPopulate);
        $this->assertEquals($dataArray, $taxRuleFromPopulate->__toArray());
        $this->assertEquals($dataArray, $taxRuleFromSetters->__toArray());
    }

    /**
     * @param array $dataArray Array with data for TaxRule
     * @dataProvider createDataProvider
     */
    public function testPopulate($dataArray)
    {
        $taxRuleFromSetters = $this->generateTaxRuleWithSetters($dataArray);
        $taxRuleFromPopulate = $this->builder->populate($taxRuleFromSetters)->create();
        $this->assertEquals($taxRuleFromSetters, $taxRuleFromPopulate);
    }

    public function createDataProvider()
    {
        return[
            'empty' => [[]],
            'all' => [
                [
                    TaxRule::ID => 1,
                    TaxRule::CODE => 'code',
                    TaxRule::CUSTOMER_TAX_CLASS_IDS => [1, 2],
                    TaxRule::PRODUCT_TAX_CLASS_IDS => [3, 4],
                    TaxRule::TAX_RATE_IDS => [1, 3],
                    TaxRule::PRIORITY => 0,
                    TaxRule::SORT_ORDER => 1,
                ]
            ],
            'one rate' => [
                [
                    TaxRule::ID => 1,
                    TaxRule::TAX_RATE_IDS => [1]
                ]
            ],
            'multiple rates' => [
                [
                    TaxRule::ID => 1,
                    TaxRule::CODE => 'code',
                    TaxRule::CUSTOMER_TAX_CLASS_IDS => [1],
                    TaxRule::PRODUCT_TAX_CLASS_IDS => [2],
                    TaxRule::TAX_RATE_IDS => [1,2,3,4],
                    TaxRule::PRIORITY => 0,
                    TaxRule::SORT_ORDER => 1,
                ]
            ],
        ];
    }

    public function testMergeDataObjects()
    {
        $taxRuleDataSomeFields = [
                TaxRule::ID => 1,
                TaxRule::CODE => 'code',
                TaxRule::TAX_RATE_IDS => [1],
        ];

        $taxRuleDataMoreFields = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'codeChanged',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [1],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [2],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 1,
        ];

        $taxRuleDataExpected = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'codeChanged',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [1],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [2],
            TaxRule::TAX_RATE_IDS => [1],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 1,
        ];

        $taxRuleExpected = $this->builder->populateWithArray($taxRuleDataExpected)->create();
        $taxRuleSomeFields = $this->builder->populateWithArray($taxRuleDataSomeFields)->create();
        $taxRuleMoreFields = $this->builder->populateWithArray($taxRuleDataMoreFields)->create();
        $taxRuleMerged = $this->builder->mergeDataObjects($taxRuleSomeFields, $taxRuleMoreFields);
        $this->assertEquals($taxRuleExpected->__toArray(), $taxRuleMerged->__toArray());
    }

    public function testMergeDataObjectsWithArray()
    {
        $taxRuleDataSomeFields = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'code',
            TaxRule::TAX_RATE_IDS => [1],
        ];

        $taxRuleDataMoreFields = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'codeChanged',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [1],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [2],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 1,
        ];

        $taxRuleDataExpected = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'codeChanged',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [1],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [2],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 1,
            TaxRule::TAX_RATE_IDS => [1],
        ];

        $taxRuleExpected = $this->builder->populateWithArray($taxRuleDataExpected)->create();
        $taxRuleSomeFields = $this->builder->populateWithArray($taxRuleDataSomeFields)->create();
        $taxRuleMerged = $this->builder->mergeDataObjectWithArray($taxRuleSomeFields, $taxRuleDataMoreFields);
        $this->assertEquals($taxRuleExpected->__toArray(), $taxRuleMerged->__toArray());
    }

    /**
     * Creates a TaxRule data object by calling setters for known keys of TaxRule that are in dataArray.
     *
     * @param array $dataArray
     * @return TaxRule
     */
    private function generateTaxRuleWithSetters($dataArray)
    {
        $this->builder->populateWithArray([]);
        if (array_key_exists(TaxRule::ID, $dataArray)) {
            $this->builder->setId($dataArray[TaxRule::ID]);
        }
        if (array_key_exists(TaxRule::CODE, $dataArray)) {
            $this->builder->setCode($dataArray[TaxRule::CODE]);
        }
        if (array_key_exists(TaxRule::CUSTOMER_TAX_CLASS_IDS, $dataArray)) {
            $this->builder->setCustomerTaxClassIds($dataArray[TaxRule::CUSTOMER_TAX_CLASS_IDS]);
        }
        if (array_key_exists(TaxRule::PRODUCT_TAX_CLASS_IDS, $dataArray)) {
            $this->builder->setProductTaxClassIds($dataArray[TaxRule::PRODUCT_TAX_CLASS_IDS]);
        }
        if (array_key_exists(TaxRule::TAX_RATE_IDS, $dataArray)) {
            $this->builder->setTaxRateIds($dataArray[TaxRule::TAX_RATE_IDS]);
        }
        if (array_key_exists(TaxRule::PRIORITY, $dataArray)) {
            $this->builder->setPriority($dataArray[TaxRule::PRIORITY]);
        }
        if (array_key_exists(TaxRule::SORT_ORDER, $dataArray)) {
            $this->builder->setSortOrder($dataArray[TaxRule::SORT_ORDER]);
        }
        return $this->builder->create();
    }
}