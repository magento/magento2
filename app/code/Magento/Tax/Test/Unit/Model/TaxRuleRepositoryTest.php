<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Api\SortOrder;
use \Magento\Tax\Model\TaxRuleRepository;

/**
 * Class TaxRuleRepositoryTest
 * @package Magento\Tax\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRuleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\TaxRuleRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRuleRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface |
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->taxRuleRegistry =
            $this->getMock(\Magento\Tax\Model\Calculation\TaxRuleRegistry::class, [], [], '', false);
        $this->taxRuleRegistry = $this->getMock(
            \Magento\Tax\Model\Calculation\TaxRuleRegistry::class,
            [],
            [],
            '',
            false
        );
        $this->searchResultFactory = $this->getMock(
            \Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->searchResultsMock = $this->getMock(
            \Magento\Tax\Api\Data\TaxRuleSearchResultsInterface::class,
            [],
            [],
            '',
            false
        );
        $this->ruleFactory = $this->getMock(\Magento\Tax\Model\Calculation\RuleFactory::class, [], [], '', false);
        $this->collectionFactory = $this->getMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resource = $this->getMock(\Magento\Tax\Model\ResourceModel\Calculation\Rule::class, [], [], '', false);
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::class,
            ['process'],
            [],
            '',
            false
        );
        $this->collectionProcessor = $this->getMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->model = new TaxRuleRepository(
            $this->taxRuleRegistry,
            $this->searchResultFactory,
            $this->ruleFactory,
            $this->collectionFactory,
            $this->resource,
            $this->extensionAttributesJoinProcessorMock,
            $this->collectionProcessor
        );
    }

    public function testGet()
    {
        $rule = $this->getMock(\Magento\Tax\Model\Calculation\Rule::class, [], [], '', false);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->assertEquals($rule, $this->model->get(10));
    }

    public function testDelete()
    {
        $rule = $this->getMock(\Magento\Tax\Model\Calculation\Rule::class, [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->delete($rule));
    }

    public function testDeleteById()
    {
        $rule = $this->getMock(\Magento\Tax\Model\Calculation\Rule::class, [], [], '', false);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);

        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->deleteById(10));
    }

    public function testSave()
    {
        $rule = $this->getMock(\Magento\Tax\Model\Calculation\Rule::class, [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);

        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->resource->expects($this->once())->method('save')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('registerTaxRule')->with($rule);
        $this->assertEquals($rule, $this->model->save($rule));
    }

    /**
     * @dataProvider saveExceptionsDataProvider
     * @param $exceptionObject
     * @param $exceptionName
     * @param $exceptionMessage
     * @throws \Exception
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveWithExceptions($exceptionObject, $exceptionName, $exceptionMessage)
    {
        $rule = $this->getMock(\Magento\Tax\Model\Calculation\Rule::class, [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);

        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->resource->expects($this->once())->method('save')->with($rule)
            ->willThrowException($exceptionObject);
        $this->taxRuleRegistry->expects($this->never())->method('registerTaxRule');

        $this->setExpectedException($exceptionName, $exceptionMessage);
        $this->model->save($rule);
    }

    public function saveExceptionsDataProvider()
    {
        return [
            [
                new \Magento\Framework\Exception\LocalizedException(__('Could not save')), \Magento\Framework\Exception\CouldNotSaveException::class,
                'Could not save'
            ], [
                new \Magento\Framework\Exception\AlreadyExistsException(__('Entity already exists')), \Magento\Framework\Exception\AlreadyExistsException::class,
                'Entity already exists'
            ], [
                new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity')), \Magento\Framework\Exception\NoSuchEntityException::class,
                'No such entity'
            ]
        ];
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $collectionMock =
            $this->getMock(\Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection::class, [], [], '', false);
            $this->getMock(\Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection::class, [], [], '', false);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $this->searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->searchResultsMock->expects($this->once())->method('setItems')->with([]);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultsMock);
        $this->assertEquals($this->searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
