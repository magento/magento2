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

use Magento\Framework\Exception\ErrorMessage;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Service\V1\Data\TaxRule;
use Magento\Tax\Service\V1\Data\TaxClass;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\TestFramework\Helper\ObjectManager;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Class TaxRuleServiceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRuleServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxRuleServiceInterface
     */
    private $taxRuleService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\TaxRuleRegistry
     */
    private $ruleRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\TaxRuleConverter
     */
    private $converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\Rule
     */
    private $ruleModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\RuleFactory
     */
    private $taxRuleModelFactoryMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\FilterBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;


    /**
     * @var \Magento\Tax\Service\V1\TaxRateServiceInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRateServiceMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TaxClassService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassServiceMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->ruleRegistryMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\TaxRuleRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\TaxRuleConverter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleModelMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rule')
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRuleModelFactoryMock = $this->getMockBuilder('\Magento\Tax\Model\Calculation\RuleFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->taxRuleModelFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->ruleModelMock));

        $taxRuleResultsBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder'
        );
        $this->filterBuilderMock = $this->getMockBuilder('\Magento\Framework\Service\V1\Data\FilterBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(
            '\Magento\Framework\Service\V1\Data\SearchCriteriaBuilder'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRateServiceMock = $this->getMockBuilder('\Magento\Tax\Service\V1\TaxRateServiceInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxClassServiceMock = $this->getMockBuilder('\Magento\Tax\Service\V1\TaxClassService')
            ->disableOriginalConstructor()
            ->getMock();

        $taxClassBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxClassBuilder');

        $customerTaxClassArray = [
            TaxClass::KEY_ID => 3,
            TaxClass::KEY_NAME => 'Some Customer Tax Class',
            TaxClass::KEY_TYPE => TaxClassModel::TAX_CLASS_TYPE_CUSTOMER,
        ];

        $productTaxClassArray = [
            TaxClass::KEY_ID => 2,
            TaxClass::KEY_NAME => 'Some Product Tax Class',
            TaxClass::KEY_TYPE => TaxClassModel::TAX_CLASS_TYPE_PRODUCT,
        ];

        $customerTaxClass = $taxClassBuilder->populateWithArray($customerTaxClassArray)->create();
        $productTaxClass = $taxClassBuilder->populateWithArray($productTaxClassArray)->create();

        $map = [
            [
                3, $customerTaxClass,
            ],
            [
                2, $productTaxClass,
            ],
        ];

        $this->taxClassServiceMock->expects($this->any())
            ->method('getTaxClass')
            ->will($this->returnValueMap($map));

        $this->taxRuleService = $this->getTaxRuleService($taxRuleResultsBuilder);
    }

    public function testDeleteTaxRule()
    {
        $this->ruleRegistryMock->expects($this->once())
            ->method('retrieveTaxRule')
            ->with(1)
            ->will($this->returnValue($this->ruleModelMock));
        $this->ruleRegistryMock->expects($this->once())
            ->method('removeTaxRule')
            ->with(1);
        $this->taxRuleService->deleteTaxRule(1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteTaxRuleRetrieveException()
    {
        $this->ruleRegistryMock->expects($this->once())
            ->method('retrieveTaxRule')
            ->with(1)
            ->will($this->throwException(new NoSuchEntityException()));
        $this->taxRuleService->deleteTaxRule(1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Bad error occurred
     */
    public function testDeleteTaxRuleDeleteException()
    {
        $this->ruleRegistryMock->expects($this->once())
            ->method('retrieveTaxRule')
            ->with(1)
            ->will($this->returnValue($this->ruleModelMock));
        $this->ruleModelMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception('Bad error occurred')));
        $this->taxRuleService->deleteTaxRule(1);
    }

    public function testUpdateTaxRate()
    {
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder
            ->setId(2)
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRuleModel')
            ->with($taxRule)
            ->will($this->returnValue($this->ruleModelMock));
        $this->ruleModelMock->expects($this->once())->method('save');

        $result = $this->taxRuleService->updateTaxRule($taxRule);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateTaxRuleNoId()
    {
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();

        $this->converterMock->expects($this->once())
            ->method('createTaxRuleModel')
            ->with($taxRule)
            ->will($this->throwException(new NoSuchEntityException()));

        $this->taxRuleService->updateTaxRule($taxRule);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testUpdateTaxRuleMissingRequiredInfo()
    {
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder
            ->setId(3)
            ->setCode(null)
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();
        $this->taxRuleService->updateTaxRule($taxRule);
    }

    public function testGetTaxRule()
    {
        $ruleId = 1;
        $expectedTaxRule = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxRule')
            ->disableOriginalConstructor()->getMock();
        $taxRuleModel = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rule')
            ->disableOriginalConstructor()->getMock();
        $this->ruleRegistryMock->expects($this->once())
            ->method('retrieveTaxRule')->with($ruleId)
            ->will($this->returnValue($taxRuleModel));
        $this->converterMock->expects($this->once())
            ->method('createTaxRuleDataObjectFromModel')->with($taxRuleModel)
            ->will($this->returnValue($expectedTaxRule));

        $taxRule = $this->taxRuleService->getTaxRule($ruleId);

        $this->assertSame($expectedTaxRule, $taxRule);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetTaxRuleNotFound()
    {
        $ruleId = 1;
        $this->ruleRegistryMock->expects($this->once())
            ->method('retrieveTaxRule')->with($ruleId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this->taxRuleService->getTaxRule($ruleId);
    }

    public function testCreateTaxRule()
    {
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRuleModel')
            ->with($taxRule)
            ->will($this->returnValue($this->ruleModelMock));
        $this->ruleModelMock->expects($this->once())->method('save');
        $expectedTaxRule = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxRule')
            ->disableOriginalConstructor()->getMock();
        $this->converterMock->expects($this->once())
            ->method('createTaxRuleDataObjectFromModel')->with($this->ruleModelMock)
            ->will($this->returnValue($expectedTaxRule));

        $result = $this->taxRuleService->createTaxRule($taxRule);

        $this->assertSame($expectedTaxRule, $result);
    }
    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateTaxRuleSpecifyingId()
    {
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder
            ->setId(9)
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();

        $this->taxRuleService->createTaxRule($taxRule);
    }

    /**
     * @dataProvider createTaxRuleMissingRequiredInfoDataProvider
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateTaxRuleMissingRequiredInfo($data, $expectedMessages)
    {
        /** @var \Magento\Tax\Service\V1\Data\TaxRuleBuilder $taxRuleBuilder */
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder->populateWithArray($data)->create();

        try {
            $this->taxRuleService->createTaxRule($taxRule);
        } catch (InputException $e) {
            $errorMessages = array_map(
                function ($error) {
                    /** @var ErrorMessage $error */
                    return $error->getMessage();
                },
                $e->getErrors()
            );
            $this->assertEquals($expectedMessages, $errorMessages);
            throw $e;
        }
    }

    public function createTaxRuleMissingRequiredInfoDataProvider()
    {
        return [
            'empty fields' => [
                [],
                [
                    'sort_order is a required field.',
                    'priority is a required field.',
                    'code is a required field.',
                    'customer_tax_class_ids is a required field.',
                    'product_tax_class_ids is a required field.',
                    'tax_rate_ids is a required field.',
                ],
            ],
            'negative fields' => [
                [
                    'customer_tax_class_ids' => [3],
                    'product_tax_class_ids' => [2],
                    'tax_rate_ids' => [1],
                    'code' => 'code',
                    'sort_order' => -14,
                    'priority' => -7,
                ],
                [
                    'The sort_order value of "-14" must be greater than or equal to 0.',
                    'The priority value of "-7" must be greater than or equal to 0.',
                ],
            ],
        ];
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateTaxRuleExceptionOnSave()
    {
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRuleModel')
            ->with($taxRule)
            ->will($this->returnValue($this->ruleModelMock));
        $this->ruleModelMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $this->taxRuleService->createTaxRule($taxRule);
    }

    public function testCreateTaxRuleInvalidTaxClassIds()
    {
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $taxRule = $taxRuleBuilder
            ->setCode('code')
            ->setCustomerTaxClassIds([2])
            ->setProductTaxClassIds([3])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();

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

    public function testSearchTaxRulesEmptyResult()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject |
         * \Magento\Framework\Service\V1\Data\SearchCriteria $mockSearchCriteria */
        $mockSearchCriteria = $this->getMockBuilder('\Magento\Framework\Service\V1\Data\SearchCriteria')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSearchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->will($this->returnValue([]));

        $mockCollection = $this->getMockBuilder('\Magento\Tax\Model\Resource\Calculation\Rule\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([])));

        $this->ruleModelMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($mockCollection));

        $mockCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));

        $taxSearchResults = $this->taxRuleService->searchTaxRules($mockSearchCriteria);

        $this->assertNotNull($taxSearchResults);
        $this->assertSame($mockSearchCriteria, $taxSearchResults->getSearchCriteria());
        $this->assertSame(0, $taxSearchResults->getTotalCount());
        $items = $taxSearchResults->getItems();
        $this->assertNotNull($items);
        $this->assertTrue(empty($items));
    }

    public function testSearchTaxRulesSingleResult()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject |
         * \Magento\Tax\Model\Resource\Calculation\Rule\Collection $mockCollection */
        $mockCollection = $this->getMockBuilder('\Magento\Tax\Model\Resource\Calculation\Rule\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getItems', 'getSize', 'addFieldToFilter', '_beforeLoad', 'getIterator'])
            ->getMock();
        /** @var \Magento\Tax\Service\V1\Data\TaxRuleBuilder $taxRuleBuilder */
        $taxRuleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        /** @var TaxRule $taxRule */
        $taxRule = $taxRuleBuilder->create();

        $taxRuleModel = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rule')
            ->disableOriginalConstructor()->getMock();
        $mockCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$taxRuleModel])));
        $mockCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));

        $filterBuilder = $this->objectManager->getObject('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $filter = $filterBuilder->setField('code')->setValue('code')->setConditionType('eq')->create();

        $filterGroupBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder');
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['filterGroupBuilder' => $filterGroupBuilder]
        );
        $sortOrderBuilder = $this->objectManager->getObject('\Magento\Framework\Service\V1\Data\SortOrderBuilder');
        $sortOrder = $sortOrderBuilder
            ->setField('id')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter([$filter])
            ->addSortOrder($sortOrder)
            ->create();

        /** @var \Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder $searchResultsBuilder */
        $searchResultsBuilder = $this->objectManager->getObject(
            '\Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder'
        );
        $expectedResults = $searchResultsBuilder->setSearchCriteria($searchCriteria)
            ->setItems([$taxRule])
            ->setTotalCount(1)
            ->create();

        $this->ruleModelMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($mockCollection));

        $this->converterMock->expects($this->once())
            ->method('createTaxRuleDataObjectFromModel')->with($taxRuleModel)
            ->will($this->returnValue($taxRule));

        $actualResults = $this->taxRuleService->searchTaxRules($searchCriteria);

        $this->assertNotNull($actualResults);
        $this->assertSame($searchCriteria, $actualResults->getSearchCriteria());
        $this->assertSame($expectedResults->getSearchCriteria(), $actualResults->getSearchCriteria());
        $this->assertSame($expectedResults->getTotalCount(), $actualResults->getTotalCount());
        $this->assertEquals($expectedResults->getItems(), $actualResults->getItems());
        $this->assertSame($taxRule, $actualResults->getItems()[0]);
    }

    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetRatesByCustomerAndProductTaxClassId()
    {
        $customerTaxClass = 0;
        $productTaxClass = 0;

        $filterOne = 'filter_one';
        $filterTwo = 'filter_two';

        /** @var \PHPUnit_Framework_MockObject_MockObject |
         * \Magento\Tax\Model\Resource\Calculation\Rule\Collection $mockCollection */
        $mockCollection = $this->getMockBuilder('\Magento\Tax\Model\Resource\Calculation\Rule\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getItems', 'getSize', 'addFieldToFilter', '_beforeLoad', 'getIterator'])
            ->getMock();

        $taxRuleModel = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rule')
            ->disableOriginalConstructor()->getMock();
        $mockCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$taxRuleModel])));
        $mockCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));

        $this->ruleModelMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($mockCollection));

        $this->filterBuilderMock->expects($this->exactly(2))
            ->method('setField')
            ->with(
                $this->logicalOr(
                    $this->equalTo(TaxRule::CUSTOMER_TAX_CLASS_IDS),
                    $this->equalTo(TaxRule::PRODUCT_TAX_CLASS_IDS)
                )
            )
            ->will($this->returnSelf());

        $this->filterBuilderMock->expects($this->exactly(2))
            ->method('setValue')
            ->with(
                $this->logicalOr(
                    $this->equalTo([$customerTaxClass]),
                    $this->equalTo([$productTaxClass])
                )
            )
            ->will($this->returnSelf());

        $this->filterBuilderMock->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($filterOne));
        $this->filterBuilderMock->expects($this->at(5))
            ->method('create')
            ->will($this->returnValue($filterTwo));

        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->with($this->logicalOr([$filterOne], [$filterTwo]))
            ->will($this->returnSelf());

        $searchCriteria = $this->getMockBuilder('\Magento\Framework\Service\V1\Data\SearchCriteria')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFilters = $this->getMockBuilder('\Magento\Framework\Service\V1\Data\Filter')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFilterGroups = $this->getMockBuilder('\Magento\Framework\Service\V1\Data\Search\FilterGroup')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFilterGroups->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue([$mockFilters]));

        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->will($this->returnValue([$mockFilterGroups]));

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteria));

        $searchResults = $this->getMockBuilder('\Magento\Tax\Service\V1\Data\TaxRuleSearchResults')
            ->disableOriginalConstructor()
            ->getMock();

        $taxRuleResultsBuilderMock= $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $mockTaxRule = $this->getMockBuilder('\Magento\Tax\Service\V1\Data\TaxRule')
            ->disableOriginalConstructor()
            ->getMock();
        $taxRules = [$mockTaxRule];

        $searchResults->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($taxRules));

        $mockTaxRule->expects($this->once())
            ->method('getTaxRateIds')
            ->will($this->returnValue([777]));

        $this->taxRateServiceMock->expects($this->once())
            ->method('getTaxRate')
            ->with(777)
            ->will($this->returnValue(888));

        $taxRuleResultsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchResults));

        $taxRuleService = $this->getTaxRuleService($taxRuleResultsBuilderMock);
        $this->assertSame(
            [888],
            $taxRuleService->getRatesByCustomerAndProductTaxClassId($customerTaxClass, $productTaxClass)
        );
    }

    /**
     * Get tax rule service
     *
     * @param $taxRuleResultsBuilder
     * @return \Magento\Tax\Service\V1\TaxRuleService
     */
    private function getTaxRuleService($taxRuleResultsBuilder)
    {
        return $this->objectManager->getObject(
            'Magento\Tax\Service\V1\TaxRuleService',
            [
                'taxRuleRegistry' => $this->ruleRegistryMock,
                'converter' => $this->converterMock,
                'taxRuleModelFactory' => $this->taxRuleModelFactoryMock,
                'taxRuleSearchResultsBuilder' => $taxRuleResultsBuilder,
                'filterBuilder' => $this->filterBuilderMock,
                'taxRateService' => $this->taxRateServiceMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'taxClassService' => $this->taxClassServiceMock
            ]
        );
    }
}
