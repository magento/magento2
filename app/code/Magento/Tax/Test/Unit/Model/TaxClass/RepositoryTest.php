<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\TaxClass;

use Magento\Framework\Api\SortOrder;
use \Magento\Tax\Model\TaxClass\Repository;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Repository */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxClassResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classModelRegistryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxClassCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchResultFactory = $this->getMock(
            '\Magento\Tax\Api\Data\TaxClassSearchResultsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchResultMock = $this->getMock(
            '\Magento\Tax\Api\Data\TaxClassSearchResultsInterface',
            [],
            [],
            '',
            false
        );

        $this->classModelRegistryMock = $this->getMock(
            '\Magento\Tax\Model\ClassModelRegistry',
            [],
            [],
            '',
            false
        );

        $this->taxClassCollectionFactory = $this->getMock(
            '\Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->taxClassResourceMock = $this->getMock('\Magento\Tax\Model\ResourceModel\TaxClass', [], [], '', false);

        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            '\Magento\Framework\Api\ExtensionAttribute\JoinProcessor',
            ['process'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            'Magento\Tax\Model\TaxClass\Repository',
            [
                'classModelRegistry' => $this->classModelRegistryMock,
                'taxClassResource' => $this->taxClassResourceMock,
                'searchResultsFactory' => $this->searchResultFactory,
                'taxClassCollectionFactory' => $this->taxClassCollectionFactory,
                'joinProcessor' => $this->extensionAttributesJoinProcessorMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $taxClass->expects($this->once())->method('getClassId')->willReturn(1);
        $this->taxClassResourceMock->expects($this->once())->method('delete')->with($taxClass);
        $this->classModelRegistryMock->expects($this->once())->method('remove')->with(1);
        $this->assertTrue($this->model->delete($taxClass));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Some Message
     */
    public function testDeleteResourceException()
    {
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
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
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
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
        $taxClass = $this->getMock('\Magento\Tax\Api\Data\TaxClassInterface');
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
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
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
        $taxClassOne = $this->getMock('\Magento\Tax\Api\Data\TaxClassInterface');
        $taxClassTwo = $this->getMock('\Magento\Tax\Api\Data\TaxClassInterface');
        $searchCriteria = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');
        $filterGroup = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filter = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $collection = $this->getMock('\Magento\Tax\Model\ResourceModel\TaxClass\Collection', [], [], '', false);
        $sortOrder = $this->getMock('\Magento\Framework\Api\SortOrder', [], [], '', false);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collection);

        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$filter]);
        $filter->expects($this->atLeastOnce())->method('getConditionType')->willReturn('eq');
        $filter->expects($this->once())->method('getField')->willReturn('field');
        $filter->expects($this->once())->method('getValue')->willReturn('value');
        $collection->expects($this->once())->method('addFieldToFilter')->with(['field'], [['eq' => 'value']]);

        $searchCriteria->expects($this->exactly(2))->method('getSortOrders')->willReturn([$sortOrder]);
        $sortOrder->expects($this->once())->method('getField')->willReturn('field');
        $sortOrder->expects($this->once())->method('getDirection')->willReturn(SortOrder::SORT_ASC);
        $collection->expects($this->once())->method('addOrder')->with('field', 'ASC');
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn(20);
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn(0);

        $collection->expects($this->any())->method('getSize')->willReturn(2);
        $collection->expects($this->any())->method('setItems')->with([$taxClassOne, $taxClassTwo]);
        $collection->expects($this->any())->method('getItems')->willReturn([$taxClassOne, $taxClassTwo]);
        $collection->expects($this->once())->method('setCurPage')->with(0);
        $collection->expects($this->once())->method('setPageSize')->with(20);

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
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('Class Name');
        $taxClass->expects($this->any())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);
        $this->classModelRegistryMock->expects($this->once())->method('registerTaxClass')->with($taxClass);

        $originTaxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
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
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Updating classType is not allowed.
     */
    public function testSaveWithInputException()
    {
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $originalTax = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $taxClass->expects($this->exactly(2))->method('getClassId')->willReturn(10);
        $this->classModelRegistryMock->expects($this->once())->method('retrieve')->with(10)->willReturn($originalTax);
        $originalTax->expects($this->once())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->once())->method('getClassType')->willReturn('PRODUCT2');
        $this->model->save($taxClass);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong
     */
    public function testSaveWithLocalizedException()
    {
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('Class Name');
        $taxClass->expects($this->atLeastOnce())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);

        $originTaxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage A class with the same name already exists for ClassType PRODUCT.
     */
    public function testSaveWithSameClassException()
    {
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('Class Name');
        $taxClass->expects($this->atLeastOnce())->method('getClassType')->willReturn('PRODUCT');
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);

        $originTaxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
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
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage One or more input exceptions have occurred.
     */
    public function testSaveWithValidateTaxClassDataException($classType)
    {
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $taxClass->expects($this->any())->method('getClassName')->willReturn('');
        $taxClass->expects($this->atLeastOnce())->method('getClassType')->willReturn($classType);
        $taxClass->expects($this->any())->method('getClassId')->willReturn(10);

        $originTaxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
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
    public function validateTaxClassDataProvider()
    {
        return [
            [''],
            ['ERROR']
        ];
    }
}
