<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Exception\CouldNotDeleteException;

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
        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([]);
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn(20);
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn(0);

        $result = $this->getMock('\Magento\Tax\Api\Data\TaxRateSearchResultsInterface');
        $collection = $this->objectManager->getCollectionMock(
            '\Magento\Tax\Model\Resource\TaxClass\Collection',
            [$taxClassOne, $taxClassTwo]
        );
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
}
