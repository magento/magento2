<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\TaxRuleSearchResultsInterface;
use Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory;
use Magento\Tax\Model\Calculation\RuleFactory;
use Magento\Tax\Model\Calculation\TaxRuleRegistry;
use Magento\Tax\Model\ResourceModel\Calculation\Rule;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory;
use Magento\Tax\Model\TaxRuleRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRuleRepositoryTest extends TestCase
{
    /**
     * @var TaxRuleRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $taxRuleRegistry;

    /**
     * @var MockObject
     */
    protected $searchResultFactory;

    /**
     * @var MockObject
     */
    protected $searchResultsMock;

    /**
     * @var MockObject
     */
    protected $ruleFactory;

    /**
     * @var MockObject
     */
    protected $collectionFactory;

    /**
     * @var MockObject
     */
    protected $resource;

    /**
     * @var MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->taxRuleRegistry =
            $this->createMock(TaxRuleRegistry::class);
        $this->taxRuleRegistry = $this->createMock(TaxRuleRegistry::class);
        $this->searchResultFactory = $this->createPartialMock(
            TaxRuleSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchResultsMock = $this->getMockForAbstractClass(TaxRuleSearchResultsInterface::class);
        $this->ruleFactory = $this->createMock(RuleFactory::class);
        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->resource = $this->createMock(Rule::class);
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            JoinProcessor::class,
            ['process']
        );
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
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
     * @throws InputException
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

    /**
     * @return array
     */
    public static function saveExceptionsDataProvider()
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
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $collectionMock =
            $this->createMock(Collection::class);
        $this->createMock(Collection::class);

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
