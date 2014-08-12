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

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Input;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Tax\Service\V1\Data\TaxRule;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class TaxRuleServiceTest tests Magento/Tax/Service/V1/TaxRuleService
 *
 */
class TaxRuleServiceTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Tax\Service\V1\Data\TaxRuleBuilder
     */
    private $taxRuleBuilder;

    /**
     * TaxRuleService
     *
     * @var \Magento\Tax\Service\V1\TaxRuleServiceInterface
     */
    private $taxRuleService;

    /**
     * Helps in creating required tax rules.
     *
     * @var TaxRuleFixtureFactory
     */
    private $taxRuleFixtureFactory;

    /**
     * Array of default tax classes ids
     *
     * Key is class name
     *
     * @var int[]
     */
    private $taxClasses;

    /**
     * Array of default tax rates ids.
     *
     * Key is rate percentage as string.
     *
     * @var int[]
     */
    private $taxRates;

    /**
     * Array of default tax rules ids.
     *
     * Key is rule code.
     *
     * @var int[]
     */
    private $taxRules;

    /**
     * TaxRateService
     *
     * @var \Magento\Tax\Service\V1\TaxRateServiceInterface
     */
    private $taxRateService;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxRuleService = $this->objectManager->get('Magento\Tax\Service\V1\TaxRuleServiceInterface');
        $this->taxRateService = $this->objectManager->get('Magento\Tax\Service\V1\TaxRateServiceInterface');
        $this->taxRuleBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $this->taxRuleFixtureFactory = new TaxRuleFixtureFactory();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRule()
    {
        // Tax rule data object created
        $taxRuleDataObject = $this->createTaxRuleDataObject();
        //Tax rule service call
        $taxRuleServiceData = $this->taxRuleService->createTaxRule($taxRuleDataObject);

        //Assertions
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRule', $taxRuleServiceData);
        $this->assertEquals($taxRuleDataObject->getCode(), $taxRuleServiceData->getCode());
        $this->assertEquals(
            $taxRuleDataObject->getCustomerTaxClassIds(),
            $taxRuleServiceData->getCustomerTaxClassIds()
        );
        $this->assertEquals($taxRuleDataObject->getProductTaxClassIds(), $taxRuleServiceData->getProductTaxClassIds());
        $this->assertEquals($taxRuleDataObject->getPriority(), $taxRuleServiceData->getPriority());
        $this->assertEquals($taxRuleDataObject->getSortOrder(), $taxRuleServiceData->getSortOrder());
        $this->assertNotNull($taxRuleServiceData->getId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRuleSpecifyingId()
    {
        $taxRuleDataObject = $this->taxRuleBuilder
            ->setId(123)
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();

        try {
            $this->taxRuleService->createTaxRule($taxRuleDataObject);
            $this->fail('Did not throw expected InputException');
        } catch (InputException $e) {
            $this->assertEquals('TaxRule ID should not be specified.', $e->getMessage());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRuleInvalidTaxClassIds()
    {
        $taxRuleData = [
            TaxRule::CODE => 'code',
            // These TaxClassIds exist, but '2' is should be a productTaxClassId and
            // '3' should be a customerTaxClassId. See MAGETWO-25683.
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [2],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [3],
            TaxRule::TAX_RATE_IDS => [1],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 0,
        ];
        // Tax rule data object created
        $taxRule = $this->taxRuleBuilder->populateWithArray($taxRuleData)->create();

        try {
            //Tax rule service call
            $this->taxRuleService->createTaxRule($taxRule);
            $this->fail('Did not throw expected InputException');
        } catch (InputException $e) {
            $expectedCustomerTaxClassIdParams = [
                'fieldName' => $taxRule::CUSTOMER_TAX_CLASS_IDS,
                'value'     => 2,
            ];
            $expectedProductTaxClassIdParams = [
                'fieldName' => $taxRule::PRODUCT_TAX_CLASS_IDS,
                'value'    => 3,
            ];

            $actualErrors = $e->getErrors();
            $this->assertEquals(2, count($actualErrors));
            $this->assertEquals($expectedCustomerTaxClassIdParams, $actualErrors[0]->getParameters());
            $this->assertEquals($expectedProductTaxClassIdParams, $actualErrors[1]->getParameters());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRuleInvalidSortOrder()
    {
        $taxRuleData = [
            TaxRule::CODE => 'code',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [3],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [2],
            TaxRule::TAX_RATE_IDS => [1],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => -1,
        ];
        // Tax rule data object created
        $taxRule = $this->taxRuleBuilder->populateWithArray($taxRuleData)->create();

        try {
            //Tax rule service call
            $this->taxRuleService->createTaxRule($taxRule);
            $this->fail('Did not throw expected InputException');
        } catch (InputException $e) {
            $expectedParams = [
                'fieldName' => taxRule::SORT_ORDER,
                'value' => -1,
                'minValue' => '0',
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
            $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $e->getRawMessage());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetTaxRuleCreatedFromService()
    {
        // Tax rule data object created
        $taxRuleDataObject = $this->createTaxRuleDataObject();
        //Tax rule service call to create rule
        $ruleId = $this->taxRuleService->createTaxRule($taxRuleDataObject)->getId();

        // Call getTaxRule and verify
        $taxRule = $this->taxRuleService->getTaxRule($ruleId);
        $this->assertEquals('code', $taxRule->getCode());
        $this->assertEquals([3], $taxRule->getCustomerTaxClassIds());
        $this->assertEquals([2], $taxRule->getProductTaxClassIds());
        $this->assertEquals([2], $taxRule->getTaxRateIds());
        $this->assertEquals(0, $taxRule->getPriority());
        $this->assertEquals(1, $taxRule->getSortOrder());
    }
    /**
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testGetTaxRuleCreatedFromModel()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get('Magento\Framework\Registry');
        /** @var $taxRuleModel \Magento\Tax\Model\Calculation\Rule */
        $taxRuleModel = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $this->assertNotNull($taxRuleModel);
        $ruleId = $taxRuleModel->getId();

        $taxRateId = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rate')->getId();
        $customerTaxClassIds = array_values(array_unique($taxRuleModel->getCustomerTaxClasses()));

        // Call getTaxRule and verify
        $taxRule = $this->taxRuleService->getTaxRule($ruleId);
        $this->assertEquals($customerTaxClassIds, $taxRule->getCustomerTaxClassIds());
        $this->assertEquals([$taxRateId], $taxRule->getTaxRateIds());
    }

    /**
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testDeleteTaxRule()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get('Magento\Framework\Registry');
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $this->assertNotNull($taxRule);
        $ruleId = $taxRule->getId();

        // Delete the new tax rate
        $this->assertTrue($this->taxRuleService->deleteTaxRule($ruleId));

        // Get the new tax rule, this should fail
        try {
            $this->taxRuleService->getTaxRule($ruleId);
            $this->fail('NoSuchEntityException expected but not thrown');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRuleId',
                'fieldValue' => $ruleId,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testDeleteTaxRateException()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get('Magento\Framework\Registry');
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $this->assertNotNull($taxRule);
        $ruleId = $taxRule->getId();

        // Delete the new tax rate
        $this->assertTrue($this->taxRuleService->deleteTaxRule($ruleId));

        // Delete the new tax rate again, this should fail
        try {
            $this->taxRuleService->deleteTaxRule($ruleId);
            $this->fail('NoSuchEntityException expected but not thrown');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRuleId',
                'fieldValue' => $ruleId,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testUpdateTaxRule()
    {

        $taxRule = $this->createTaxRuleDataObject();
        //Tax rule service call
        $taxRuleServiceData = $this->taxRuleService->createTaxRule($taxRule);

        $updatedTaxRule = $this->taxRuleBuilder->populate($taxRuleServiceData)
            ->setCode('updated code')
            ->create();

        $this->taxRuleService->updateTaxRule($updatedTaxRule);
        $retrievedRule = $this->taxRuleService->getTaxRule($taxRuleServiceData->getId());

        //Assertion
        $this->assertEquals($updatedTaxRule->__toArray(), $retrievedRule->__toArray());
        $this->assertNotEquals($taxRule->__toArray(), $retrievedRule->__toArray());
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage taxRuleId =
     */
    public function testUpdateTaxRuleNoId()
    {
        $this->taxRuleService->updateTaxRule($this->createTaxRuleDataObject());
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage code
     */
    public function testUpdateTaxRuleMissingRequiredFields()
    {
        $taxRuleServiceData = $this->taxRuleService->createTaxRule($this->createTaxRuleDataObject());
        $updatedTaxRule = $this->taxRuleBuilder->populate($taxRuleServiceData)
            ->setCode(null)
            ->create();

        $this->taxRuleService->updateTaxRule($updatedTaxRule);
    }

    /**
     *
     * @param Filter[] $filters
     * @param Filter[] $filterGroup
     * @param string[] $expectedResultCodes The codes of the tax rules that are expected to be found
     *
     * @magentoDbIsolation enabled
     * @dataProvider searchTaxRulesDataProvider
     */
    public function testSearchTaxRules($filters, $filterGroup, $expectedRuleCodes)
    {
        $this->setUpDefaultRules();

        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Service\V1\Data\SearchCriteriaBuilder');
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }
        $searchCriteria = $searchBuilder->create();

        $searchResults = $this->taxRuleService->searchTaxRules($searchCriteria);
        $items = [];
        foreach ($expectedRuleCodes as $ruleCode) {
            $ruleId = $this->taxRules[$ruleCode];
            $items[] = $this->taxRuleService->getTaxRule($ruleId);
        }

        /** @var TaxRuleSearchResultsBuilder $resultsBuilder */
        $resultsBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder');
        $expectedResult = $resultsBuilder->setItems($items)
            ->setTotalCount(count($items))
            ->setSearchCriteria($searchCriteria)
            ->create();
        $this->assertEquals($expectedResult, $searchResults);
        $this->tearDownDefaultRules();
    }

    /**
     *
     * @magentoDbIsolation enabled
     */
    public function testGetRatesByCustomerAndProductTaxClassId()
    {
        $this->setUpDefaultRules();
        $taxRateIds = $this->taxRuleService->getTaxRule(current($this->taxRules))->getTaxRateIds();
        $expectedRates = [];
        foreach ($taxRateIds as $rateId) {
            $expectedRates[] = $this->taxRateService->getTaxRate($rateId);
        }
        $rates = $this->taxRuleService->getRatesByCustomerAndProductTaxClassId(
            $this->taxClasses['DefaultCustomerClass'],
            $this->taxClasses['DefaultProductClass']
        );

        $this->assertCount(2, $rates);
        $this->assertEquals($expectedRates, $rates);
    }

    public function searchTaxRulesDataProvider()
    {
        $filterBuilder = Bootstrap::getObjectManager()->create('\Magento\Framework\Service\V1\Data\FilterBuilder');

        return [
            'code eq "Default Rule"' => [
                [$filterBuilder->setField(TaxRule::CODE)->setValue('Default Rule')->create()],
                null,
                ['Default Rule']
            ],
            'customer_tax_class_ids eq 3 AND priority eq 0' => [
                [
                    $filterBuilder->setField(TaxRule::CUSTOMER_TAX_CLASS_IDS)->setValue(3)->create(),
                    $filterBuilder->setField(TaxRule::PRIORITY)->setValue('0')->create(),
                ],
                [],
                ['Default Rule', 'Higher Rate Rule']
            ],
            'code eq "Default Rule" OR code eq "Higher Rate Rule"' => [
                [],
                [
                    $filterBuilder->setField(TaxRule::CODE)->setValue('Default Rule')->create(),
                    $filterBuilder->setField(TaxRule::CODE)->setValue('Higher Rate Rule')->create(),
                ],
                ['Default Rule', 'Higher Rate Rule']
            ],
            'code like "%Rule"' => [
                [
                    $filterBuilder->setField(TaxRule::CODE)->setValue('%Rule')->setConditionType('like')
                        ->create()
                ],
                [],
                ['Default Rule', 'Higher Rate Rule']
            ],
        ];
    }

    /**
     * Helper function that sets up some default rules
     */
    private function setUpDefaultRules()
    {
        $this->taxClasses = $this->taxRuleFixtureFactory->createTaxClasses([
                ['name' => 'DefaultCustomerClass', 'type' => ClassModel::TAX_CLASS_TYPE_CUSTOMER],
                ['name' => 'DefaultProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
                ['name' => 'HigherProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
            ]);

        $this->taxRates = $this->taxRuleFixtureFactory->createTaxRates([
                ['percentage' => 7.5, 'country' => 'US', 'region' => 42],
                ['percentage' => 7.5, 'country' => 'US', 'region' => 12], // Default store rate
            ]);

        $higherRates = $this->taxRuleFixtureFactory->createTaxRates([
                ['percentage' => 22, 'country' => 'US', 'region' => 42],
                ['percentage' => 10, 'country' => 'US', 'region' => 12], // Default store rate
            ]);

        $this->taxRules = $this->taxRuleFixtureFactory->createTaxRules([
                [
                    'code' => 'Default Rule',
                    'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                    'product_tax_class_ids' => [$this->taxClasses['DefaultProductClass']],
                    'tax_rate_ids' => array_values($this->taxRates),
                    'sort_order' => 0,
                    'priority' => 0,
                    'calculate_subtotal' => 1,
                ],
                [
                    'code' => 'Higher Rate Rule',
                    'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                    'product_tax_class_ids' => [$this->taxClasses['HigherProductClass']],
                    'tax_rate_ids' => array_values($higherRates),
                    'sort_order' => 0,
                    'priority' => 0,
                    'calculate_subtotal' => 1,
                ],
                [
                    'code' => 'Highest Rate',
                    'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                    'product_tax_class_ids' => [$this->taxClasses['HigherProductClass']],
                    'tax_rate_ids' => array_values($higherRates),
                    'sort_order' => 1,
                    'priority' => 1,
                    'calculate_subtotal' => 0,
                ],
            ]);

        // For cleanup
        $this->taxRates = array_merge($this->taxRates, $higherRates);
    }

    /**
     * Helper function that tears down some default rules
     */
    private function tearDownDefaultRules()
    {
        $this->taxRuleFixtureFactory->deleteTaxRules(array_values($this->taxRules));
        $this->taxRuleFixtureFactory->deleteTaxRates(array_values($this->taxRates));
        $this->taxRuleFixtureFactory->deleteTaxClasses(array_values($this->taxClasses));
    }

    /**
     * Creates Tax Rule Data Object
     *
     * @return \Magento\Tax\Service\V1\Data\TaxRule
     */
    private function createTaxRuleDataObject()
    {
        return $this->taxRuleBuilder
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();
    }
}
