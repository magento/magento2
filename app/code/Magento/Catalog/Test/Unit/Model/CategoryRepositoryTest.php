<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\CategoryRepository\PopulateWithValues;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepositoryTest extends TestCase
{
    /**
     * @var CategoryRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $categoryFactoryMock;

    /**
     * @var  MockObject
     */
    protected $categoryResourceMock;

    /**
     * @var MockObject
     */
    protected $extensibleDataObjectConverterMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var PopulateWithValues|MockObject
     */
    private $populateWithValuesMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->categoryFactoryMock = $this->createPartialMock(
            CategoryFactory::class,
            ['create']
        );
        $this->categoryResourceMock =
            $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->extensibleDataObjectConverterMock = $this
            ->getMockBuilder(ExtensibleDataObjectConverter::class)
            ->onlyMethods(['toNestedArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $metadataMock = $this->createMock(EntityMetadata::class);
        $metadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(CategoryInterface::class)
            ->willReturn($metadataMock);

        $this->populateWithValuesMock = $this
            ->getMockBuilder(PopulateWithValues::class)
            ->onlyMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = new ObjectManager($this);
        $objects = [
            [
                PopulateWithValues::class,
                $this->createMock(PopulateWithValues::class)
            ]
        ];
        $objectHelper->prepareObjectManager($objects);

        $this->model = (new ObjectManager($this))->getObject(
            CategoryRepository::class,
            [
                'categoryFactory' => $this->categoryFactoryMock,
                'categoryResource' => $this->categoryResourceMock,
                'storeManager' => $this->storeManagerMock,
                'metadataPool' => $this->metadataPoolMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
                'populateWithValues' => $this->populateWithValuesMock,
            ]
        );
    }

    public function testGet()
    {
        $categoryId = 5;
        $categoryMock = $this->createMock(CategoryModel::class);
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

    public function testGetWhenCategoryDoesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity with id = 5');
        $categoryId = 5;
        $categoryMock = $this->createMock(CategoryModel::class);
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
    public static function filterExtraFieldsOnUpdateCategoryDataProvider()
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
        $categoryMock = $this->createMock(CategoryModel::class);
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
            ->willReturn($categoryData);
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $this->populateWithValuesMock->expects($this->once())->method('execute')->with($categoryMock, $dataForSave);
        $this->categoryResourceMock->expects($this->once())
            ->method('save')
            ->willReturn(DataObject::class);
        $this->assertEquals($categoryMock, $this->model->save($categoryMock));
    }

    public function testCreateNewCategory()
    {
        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $categoryId = null;
        $parentCategoryId = 15;
        $newCategoryId = 25;
        $categoryData = ['level' => '1', 'path' => '1/2', 'parent_id' => 1, 'name' => 'category'];
        $dataForSave = ['store_id' => 1, 'name' => 'category', 'path' => 'path', 'parent_id' => 15, 'level' => null];
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($categoryData);
        $categoryMock = $this->createMock(CategoryModel::class);
        $parentCategoryMock = $this->createMock(CategoryModel::class);
        $callCount = 0;
        $categoryMock->expects($this->any())->method('getId')
            ->willReturnCallback(function () use (&$callCount, $categoryId, $newCategoryId) {
                return $callCount++ === 0 ? $categoryId : $newCategoryId;
            });
        $this->categoryFactoryMock->expects($this->exactly(2))->method('create')->willReturn($parentCategoryMock);
        $parentCategoryMock->expects($this->atLeastOnce())->method('getId')->willReturn($parentCategoryId);

        $categoryMock->expects($this->once())->method('getParentId')->willReturn($parentCategoryId);
        $parentCategoryMock->expects($this->once())->method('getPath')->willReturn('path');
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $this->categoryResourceMock->expects($this->once())
            ->method('save')
            ->willReturn(DataObject::class);
        $this->populateWithValuesMock->expects($this->once())->method('execute')->with($categoryMock, $dataForSave);
        $this->assertEquals($categoryMock, $this->model->save($categoryMock));
    }

    public function testSaveWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('Could not save category');
        $categoryId = 5;
        $categoryMock = $this->createMock(CategoryModel::class);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn([]);
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
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $categoryId = 5;
        $categoryMock = $this->createMock(CategoryModel::class);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn([]);
        $objectMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getFrontend', 'getLabel'])
            ->disableOriginalConstructor()
            ->getMock();
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

    /**
     * @return array
     */
    public static function saveWithValidateCategoryExceptionDataProvider()
    {
        return [
            [
                true, CouldNotSaveException::class,
                'Could not save category: The "ValidateCategoryTest" attribute is required. Enter and try again.'
            ], [
                'Something went wrong', CouldNotSaveException::class,
                'Could not save category: Something went wrong'
            ]
        ];
    }

    public function testDelete()
    {
        $categoryMock = $this->createMock(CategoryModel::class);
        $this->assertTrue($this->model->delete($categoryMock));
    }

    /**
     * @throws StateException
     */
    public function testDeleteWithException()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('Cannot delete category with id');
        $categoryMock = $this->createMock(CategoryModel::class);
        $this->categoryResourceMock->expects($this->once())->method('delete')->willThrowException(new \Exception());
        $this->model->delete($categoryMock);
    }

    public function testDeleteByIdentifier()
    {
        $categoryId = 5;
        $categoryMock = $this->createMock(CategoryModel::class);
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

    public function testDeleteByIdentifierWithException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity with id = 5');
        $categoryId = 5;
        $categoryMock = $this->createMock(CategoryModel::class);
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
