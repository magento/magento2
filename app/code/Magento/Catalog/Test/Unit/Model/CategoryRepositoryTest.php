<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensibleDataObjectConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    protected function setUp()
    {
        $this->categoryFactoryMock = $this->getMock(
            \Magento\Catalog\Model\CategoryFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->categoryResourceMock =
            $this->getMock(\Magento\Catalog\Model\ResourceModel\Category::class, [], [], '', false);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->extensibleDataObjectConverterMock = $this
            ->getMockBuilder(\Magento\Framework\Api\ExtensibleDataObjectConverter::class)
            ->setMethods(['toNestedArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $metadataMock = $this->getMock(
            \Magento\Framework\EntityManager\EntityMetadata::class,
            [],
            [],
            '',
            false
        );
        $metadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $this->metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\CategoryInterface::class)
            ->willReturn($metadataMock);

        $this->model = (new ObjectManager($this))->getObject(
            CategoryRepository::class,
            [
                'categoryFactory' => $this->categoryFactoryMock,
                'categoryResource' => $this->categoryResourceMock,
                'storeManager' => $this->storeManagerMock,
                'metadataPool' => $this->metadataPoolMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
            ]
        );
    }

    public function testGet()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $categoryMock->expects(
            $this->once()
        )->method('getId')->willReturn(
            $categoryId
        );
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );

        $this->assertEquals($categoryMock, $this->model->get($categoryId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 5
     */
    public function testGetWhenCategoryDoesNotExist()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $categoryMock->expects(
            $this->once()
        )->method('getId')->willReturn(null);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );

        $this->assertEquals($categoryMock, $this->model->get($categoryId));
    }

    /**
     * @return array
     */
    public function filterExtraFieldsOnUpdateCategoryDataProvider()
    {
        return [
            [
                3,
                ['level' => '1', 'path' => '1/2', 'parent_id' => 1, 'name' => 'category'],
                [
                    'store_id' => 1,
                    'name' => 'category',
                    'entity_id' => null
                ]
            ],
            [
                4,
                ['level' => '1', 'path' => '1/2', 'image' => ['categoryImage'], 'name' => 'category'],
                [
                    'store_id' => 1,
                    'name' => 'category',
                    'entity_id' => null
                ]
            ]
        ];
    }

    /**
     * @param $categoryId
     * @param $categoryData
     * @param $dataForSave
     * @dataProvider filterExtraFieldsOnUpdateCategoryDataProvider
     */
    public function testFilterExtraFieldsOnUpdateCategory($categoryId, $categoryData, $dataForSave)
    {
        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false, true, true);
        $categoryMock->expects(
            $this->atLeastOnce()
        )->method('getId')->willReturn($categoryId);
        $this->categoryFactoryMock->expects(
            $this->exactly(2)
        )->method('create')->willReturn(
            $categoryMock
        );
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($categoryData));
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $categoryMock->expects($this->once())->method('addData')->with($dataForSave);
        $this->categoryResourceMock->expects($this->once())
            ->method('save')
            ->willReturn(\Magento\Framework\DataObject::class);
        $this->assertEquals($categoryMock, $this->model->save($categoryMock));
    }

    public function testCreateNewCategory()
    {
        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $categoryId = null;
        $parentCategoryId = 15;
        $newCategoryId = 25;
        $categoryData = ['level' => '1', 'path' => '1/2', 'parent_id' => 1, 'name' => 'category'];
        $dataForSave = ['store_id' => 1, 'name' => 'category', 'path' => 'path', 'parent_id' => 15,];
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($categoryData));
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false, true, true);
        $parentCategoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false, true, true);
        $categoryMock->expects($this->any())->method('getId')
            ->will($this->onConsecutiveCalls($categoryId, $newCategoryId));
        $this->categoryFactoryMock->expects($this->exactly(2))->method('create')->willReturn($parentCategoryMock);
        $parentCategoryMock->expects($this->atLeastOnce())->method('getId')->willReturn($parentCategoryId);

        $categoryMock->expects($this->once())->method('getParentId')->willReturn($parentCategoryId);
        $parentCategoryMock->expects($this->once())->method('getPath')->willReturn('path');
        $categoryMock->expects($this->once())->method('addData')->with($dataForSave);
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $this->categoryResourceMock->expects($this->once())
            ->method('save')
            ->willReturn(\Magento\Framework\DataObject::class);
        $this->assertEquals($categoryMock, $this->model->save($categoryMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save category
     */
    public function testSaveWithException()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false, true, true);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue([]));
        $categoryMock->expects(
            $this->atLeastOnce()
        )->method('getId')->willReturn($categoryId);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects($this->once())->method('validate')->willReturn([42 => 'Testing an exception.']);
        $this->model->save($categoryMock);
    }

    /**
     * @dataProvider saveWithValidateCategoryExceptionDataProvider
     */
    public function testSaveWithValidateCategoryException($error, $expectedException, $expectedExceptionMessage)
    {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $categoryId = 5;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue([]));
        $objectMock = $this->getMock(\Magento\Framework\DataObject::class, ['getFrontend', 'getLabel'], [], '', false);
        $categoryMock->expects(
            $this->atLeastOnce()
        )->method('getId')->willReturn($categoryId);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $objectMock->expects($this->any())->method('getFrontend')->willReturn($objectMock);
        $objectMock->expects($this->any())->method('getLabel')->willReturn('ValidateCategoryTest');
        $categoryMock->expects($this->once())->method('validate')->willReturn([42 => $error]);
        $this->categoryResourceMock->expects($this->any())->method('getAttribute')->with(42)->willReturn($objectMock);
        $categoryMock->expects($this->never())->method('unsetData');
        $this->model->save($categoryMock);
    }

    public function saveWithValidateCategoryExceptionDataProvider()
    {
        return [
            [
                true, \Magento\Framework\Exception\CouldNotSaveException::class,
                'Could not save category: Attribute "ValidateCategoryTest" is required.',
            ], [
                'Something went wrong', \Magento\Framework\Exception\CouldNotSaveException::class,
                'Could not save category: Something went wrong'
            ]
        ];
    }

    public function testDelete()
    {
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false, true, true);
        $this->assertTrue($this->model->delete($categoryMock));
    }

    /**
     * @throws \Magento\Framework\Exception\StateException
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete category with id
     */
    public function testDeleteWithException()
    {
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false, true, true);
        $this->categoryResourceMock->expects($this->once())->method('delete')->willThrowException(new \Exception());
        $this->model->delete($categoryMock);
    }

    public function testDeleteByIdentifier()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $categoryMock->expects(
            $this->any()
        )->method('getId')->willReturn(
            $categoryId
        );
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );
        $this->assertTrue($this->model->deleteByIdentifier($categoryId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 5
     */
    public function testDeleteByIdentifierWithException()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $categoryMock->expects(
            $this->once()
        )->method('getId')->willReturn(null);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );
        $this->model->deleteByIdentifier($categoryId);
    }
}
