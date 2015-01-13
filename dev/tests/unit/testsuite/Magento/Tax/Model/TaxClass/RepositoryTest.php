<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Model\Exception as ModelException;
use Magento\Framework\Api\SearchCriteria;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Repository */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxClassResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classModelRegistryMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxClassCollectionFactory;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->searchResultBuilder = $this->getMock(
            '\Magento\Tax\Api\Data\TaxClassSearchResultsDataBuilder',
            [
                'setSearchCriteria', 'setTotalCount', 'setItems', 'create'
            ],
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
            '\Magento\Tax\Model\Resource\TaxClass\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->taxClassResourceMock = $this->getMock('\Magento\Tax\Model\Resource\TaxClass', [], [], '', false);
        $this->model = $this->objectManager->getObject(
            'Magento\Tax\Model\TaxClass\Repository',
            [
                'classModelRegistry' => $this->classModelRegistryMock,
                'taxClassResource' => $this->taxClassResourceMock,
                'searchResultsBuilder' => $this->searchResultBuilder,
                'taxClassCollectionFactory' => $this->taxClassCollectionFactory
            ]
        );
    }

    public function testDelete()
    {
        $taxClass = $this->getMock('\Magento\Tax\Model\ClassModel', [], [], '', false);
        $taxClass->expects($this->once())->method('getClassId')->willReturn(1);
        $this->taxClassResourceMock->expects($this->once())->method('delete')->with($taxClass);
        $this->classModelRegistryMock->expects($this->once())->method('remove')->with(1);
        $this->assertTrue($this->model->delete($taxClass));
    }

    /**
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
            ->willThrowException(new CouldNotDeleteException('Some Message'));
        $this->model->delete($taxClass);
    }

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

    public function testGetList()
    {
        $taxClassOne = $this->getMock('\Magento\Tax\Api\Data\TaxClassInterface');
        $taxClassTwo = $this->getMock('\Magento\Tax\Api\Data\TaxClassInterface');
        $searchCriteria = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');
        $filterGroup = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filter = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $collection = $this->getMock('\Magento\Tax\Model\Resource\TaxClass\Collection', [], [], '', false);
        $sortOrder = $this->getMock('\Magento\Framework\Api\SortOrder', [], [], '', false);

        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$filter]);
        $filter->expects($this->atLeastOnce())->method('getConditionType')->willReturn('eq');
        $filter->expects($this->once())->method('getField')->willReturn('field');
        $filter->expects($this->once())->method('getValue')->willReturn('value');
        $collection->expects($this->once())->method('addFieldToFilter')->with(['field'], [['eq' => 'value']]);

        $searchCriteria->expects($this->exactly(2))->method('getSortOrders')->willReturn([$sortOrder]);
        $sortOrder->expects($this->once())->method('getField')->willReturn('field');
        $sortOrder->expects($this->once())->method('getDirection')->willReturn(SearchCriteria::SORT_ASC);
        $collection->expects($this->once())->method('addOrder')->with('field', 'ASC');
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn(20);
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn(0);

        $result = $this->getMock('\Magento\Tax\Api\Data\TaxRateSearchResultsInterface');
        $collection->expects($this->any())->method('getSize')->willReturn(2);
        $collection->expects($this->any())->method('setItems')->with([$taxClassOne, $taxClassTwo]);
        $collection->expects($this->once())->method('setCurPage')->with(0);
        $collection->expects($this->once())->method('setPageSize')->with(20);

        $this->searchResultBuilder->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $this->searchResultBuilder->expects($this->once())->method('setTotalCount')->with(2);
        $this->searchResultBuilder->expects($this->once())->method('create')->willReturn($result);
        $this->taxClassCollectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $this->assertEquals($result, $this->model->getList($searchCriteria));
    }

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
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Something went wrong
     */
    public function testSaveWithModelException()
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
            ->willThrowException(new ModelException("Something went wrong"));
        $this->model->save($taxClass);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
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
            ->willThrowException(new ModelException(\Magento\Tax\Model\Resource\TaxClass::UNIQUE_TAX_CLASS_MSG));
        $this->model->save($taxClass);
    }

    /**
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

    public function validateTaxClassDataProvider()
    {
        return [
            [''],
            ['ERROR']
        ];
    }
}
