<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Tax\Model\TaxRuleRepository;

/**
 * Class TaxRuleRepositoryTest
 * @package Magento\Tax\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRuleRepositoryTest extends \PHPUnit\Framework\TestCase
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
            $this->createMock(\Magento\Tax\Model\Calculation\TaxRuleRegistry::class);
        $this->taxRuleRegistry = $this->createMock(\Magento\Tax\Model\Calculation\TaxRuleRegistry::class);
        $this->searchResultFactory = $this->createPartialMock(
            \Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchResultsMock = $this->createMock(\Magento\Tax\Api\Data\TaxRuleSearchResultsInterface::class);
        $this->ruleFactory = $this->createMock(\Magento\Tax\Model\Calculation\RuleFactory::class);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory::class,
            ['create']
        );
        $this->resource = $this->createMock(\Magento\Tax\Model\ResourceModel\Calculation\Rule::class);
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::class,
            ['process']
        );
        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
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
        $rule = $this->createMock(\Magento\Tax\Model\Calculation\Rule::class);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->assertEquals($rule, $this->model->get(10));
    }

    public function testDelete()
    {
        $rule = $this->createMock(\Magento\Tax\Model\Calculation\Rule::class);
        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->delete($rule));
    }

    public function testDeleteById()
    {
        $rule = $this->createMock(\Magento\Tax\Model\Calculation\Rule::class);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);

        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->deleteById(10));
    }

    public function testSave()
    {
        $rule = $this->createMock(\Magento\Tax\Model\Calculation\Rule::class);
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
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws NoSuchEntityException
     */
    public function testSaveWithExceptions($exceptionObject, $exceptionName, $exceptionMessage)
    {
        $rule = $this->createMock(\Magento\Tax\Model\Calculation\Rule::class);
        $rule->expects($this->once())->method('getId')->willReturn(10);

        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->resource->expects($this->once())->method('save')->with($rule)
            ->willThrowException($exceptionObject);
        $this->taxRuleRegistry->expects($this->never())->method('registerTaxRule');

        $this->expectException($exceptionName);
        $this->expectExceptionMessage($exceptionMessage);
        $this->model->save($rule);
    }

    public function saveExceptionsDataProvider()
    {
        return [
            [
                new LocalizedException(__('Could not save')), CouldNotSaveException::class,
                'Could not save'
            ], [
                new AlreadyExistsException(__('Entity already exists')), AlreadyExistsException::class,
                'Entity already exists'
            ], [
                new NoSuchEntityException(__('No such entity')), NoSuchEntityException::class,
                'No such entity'
            ]
        ];
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $collectionMock =
            $this->createMock(\Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection::class);
        $this->createMock(\Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection::class);

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
