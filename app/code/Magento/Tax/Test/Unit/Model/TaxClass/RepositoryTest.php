<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\TaxClass;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\Data\TaxClassSearchResultsInterface;
use Magento\Tax\Api\Data\TaxClassSearchResultsInterfaceFactory;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ClassModelRegistry;
use Magento\Tax\Model\ResourceModel\TaxClass;
use Magento\Tax\Model\ResourceModel\TaxClass\Collection;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory;
use Magento\Tax\Model\TaxClass\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /** @var  Repository */
    protected $model;

    /**
     * @var MockObject
     */
    protected $searchResultFactory;

    /**
     * @var MockObject
     */
    protected $searchResultMock;

    /**
     * @var MockObject
     */
    protected $taxClassResourceMock;

    /**
     * @var MockObject
     */
    protected $classModelRegistryMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $taxClassCollectionFactory;

    /**
     * @var MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->searchResultFactory = $this->createPartialMock(
            TaxClassSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchResultMock = $this->getMockForAbstractClass(TaxClassSearchResultsInterface::class);

        $this->classModelRegistryMock = $this->createMock(ClassModelRegistry::class);

        $this->taxClassCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->taxClassResourceMock = $this->createMock(TaxClass::class);

        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            JoinProcessor::class,
            ['process']
        );
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
        );
        $this->model = $this->objectManager->getObject(
            Repository::class,
            [
                'classModelRegistry' => $this->classModelRegistryMock,
                'taxClassResource' => $this->taxClassResourceMock,
                'searchResultsFactory' => $this->searchResultFactory,
                'taxClassCollectionFactory' => $this->taxClassCollectionFactory,
                'joinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'collectionProcessor' => $this->collectionProcessor
            ]
        );
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $taxClass = $this->createMock(ClassModel::class);
        $taxClass->expects($this->once())->method('getClassId')->willReturn(1);
        $this->taxClassResourceMock->expects($this->once())->method('delete')->with($taxClass);
        $this->classModelRegistryMock->expects($this->once())->method('remove')->with(1);
        $this->assertTrue($this->model->delete($taxClass));
    }

    /**
     * @return void
     */
    public function testDeleteResourceException()
    {
        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage('Some Message');
        $taxClass = $this->createMock(ClassModel::class);
        $taxClass->expects($this->once())->method('getClassId')->willReturn(1);
        $this->taxClassResourceMock
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new CouldNotDeleteException(__('Some Message')));
        $this->model->delete($taxClass);
    }

    /**
     * @return void
     */
    public function testDeleteWithException()
    {
        $taxClass = $this->createMock(ClassModel::class);
        $taxClass->expects($this->once())->method('getClassId')->willReturn(1);
        $this->taxClassResourceMock
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('Some Message'));
        $this->assertFalse($this->model->delete($taxClass));
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $taxClass = $this->getMockForAbstractClass(TaxClassInterface::class);
        $classId = 1;
        $this->classModelRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with($classId)
            ->willReturn($taxClass);

        $this->assertEquals($taxClass, $this->model->get($classId));
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $taxClass = $this->createMock(ClassModel::class);
        $classId = 1;
        $this->classModelRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with($classId)
            ->willReturn($taxClass);

        $taxClass->expects($this->once())->method('getClassId')->willReturn(1);
        $this->taxClassResourceMock->expects($this->once())->method('delete')->with($taxClass);
        $this->classModelRegistryMock->expects($this->once())->method('remove')->with(1);

        $this->assertTrue($this->model->deleteById($classId));
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $taxClassOne = $this->getMockForAbstractClass(TaxClassInterface::class);
        $taxClassTwo = $this->getMockForAbstractClass(TaxClassInterface::class);
        $searchCriteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $collection = $this->getMockBuilder(Collection::class)
            ->addMethods(['setItems'])
            ->onlyMethods(['getSize', 'getItems'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collection);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

        $collection->expects($this->any())->method('getSize')->willReturn(2);
        $collection->expects($this->any())->method('setItems')->with([$taxClassOne, $taxClassTwo]);
        $collection->expects($this->any())->method('getItems')->willReturn([$taxClassOne, $taxClassTwo]);

        $this->searchResultMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $this->searchResultMock->expects($this->once())->method('setTotalCount')->with(2);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultMock);
        $this->taxClassCollectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $this->assertEquals($this->searchResultMock, $this->model->getList($searchCriteria));
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $taxClass = $this->createMock(ClassModel::class);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('Class Name');
        $taxClass->expects($this->any())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);
        $this->classModelRegistryMock->expects($this->once())->method('registerTaxClass')->with($taxClass);

        $originTaxClass = $this->createMock(ClassModel::class);
        $originTaxClass->expects($this->once())->method('getClassType')->willReturn('PRODUCT');

        $this->classModelRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with(10)
            ->willReturn($originTaxClass);

        $this->taxClassResourceMock->expects($this->once())->method('save')->with($taxClass);
        $this->assertEquals(10, $this->model->save($taxClass));
    }

    /**
     * @return void
     */
    public function testSaveWithInputException()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Updating classType is not allowed.');
        $taxClass = $this->createMock(ClassModel::class);
        $originalTax = $this->createMock(ClassModel::class);
        $taxClass->expects($this->exactly(2))->method('getClassId')->willReturn(10);
        $this->classModelRegistryMock->expects($this->once())->method('retrieve')->with(10)->willReturn($originalTax);
        $originalTax->expects($this->once())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->once())->method('getClassType')->willReturn('PRODUCT2');
        $this->model->save($taxClass);
    }

    /**
     * @return void
     */
    public function testSaveWithLocalizedException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong');
        $taxClass = $this->createMock(ClassModel::class);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('Class Name');
        $taxClass->expects($this->atLeastOnce())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);

        $originTaxClass = $this->createMock(ClassModel::class);
        $originTaxClass->expects($this->once())->method('getClassType')->willReturn('PRODUCT');

        $this->classModelRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with(10)
            ->willReturn($originTaxClass);

        $this->taxClassResourceMock->expects($this->once())->method('save')->with($taxClass)
            ->willThrowException(new LocalizedException(__("Something went wrong")));
        $this->model->save($taxClass);
    }

    /**
     * @return void
     */
    public function testSaveWithSameClassException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('A class with the same name already exists for ClassType PRODUCT.');
        $taxClass = $this->createMock(ClassModel::class);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('Class Name');
        $taxClass->expects($this->atLeastOnce())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);

        $originTaxClass = $this->createMock(ClassModel::class);
        $originTaxClass->expects($this->once())->method('getClassType')->willReturn('PRODUCT');

        $this->classModelRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with(10)
            ->willReturn($originTaxClass);

        $this->taxClassResourceMock->expects($this->once())->method('save')->with($taxClass)
            ->willThrowException(new LocalizedException(__('Class name and class type')));
        $this->model->save($taxClass);
    }

    /**
     * @param string $classType
     * @return void
     * @dataProvider validateTaxClassDataProvider
     */
    public function testSaveWithValidateTaxClassDataException($classType)
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('One or more input exceptions have occurred.');
        $taxClass = $this->createMock(ClassModel::class);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('');
        $taxClass->expects($this->atLeastOnce())->method('getClassType')->willReturn($classType);
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);

        $originTaxClass = $this->createMock(ClassModel::class);
        $originTaxClass->expects($this->once())->method('getClassType')->willReturn($classType);

        $this->classModelRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with(10)
            ->willReturn($originTaxClass);
        $this->model->save($taxClass);
    }

    /**
     * @return array
     */
    public static function validateTaxClassDataProvider()
    {
        return [
            [''],
            ['ERROR']
        ];
    }
}
