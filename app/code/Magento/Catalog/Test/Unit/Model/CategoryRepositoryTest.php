<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
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
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        $this->extensibleDataObjectConverterMock = $this->createPartialMock(
            ExtensibleDataObjectConverter::class,
            ['toNestedArray']
        );

        $metadataMock = $this->createMock(EntityMetadata::class);
        $metadataMock->method('getLinkField')->willReturn('entity_id');

        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(CategoryInterface::class)
            ->willReturn($metadataMock);

        $this->populateWithValuesMock = $this->createPartialMock(
            PopulateWithValues::class,
            ['execute']
        );

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
     */
    #[DataProvider('filterExtraFieldsOnUpdateCategoryDataProvider')]
    public function testFilterExtraFieldsOnUpdateCategory($categoryId, $categoryData, $dataForSave)
    {
        $this->storeMock->method('getId')->willReturn(1);
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
        $this->storeMock->method('getId')->willReturn(1);
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

    public static function saveStoreScopeDataProvider(): array
    {
        return [
            'explicit store differs from current' => [1, ['store_id' => 3], 3],
            'explicit store with current store 0' => [0, ['store_id' => 3], 3],
            'explicit store 0 with current store' => [1, ['store_id' => 0], 0],
            'no explicit store uses current' => [2, [], 2],
            'no explicit store with current store 0' => [0, [], 0],
        ];
    }

    #[DataProvider('saveStoreScopeDataProvider')]
    public function testSaveExistingCategoryUsesExpectedStore(
        int $currentStoreId,
        array $data,
        int $expectedStoreId
    ): void {
        $categoryId = 10;
        $this->storeMock->method('getId')->willReturn($currentStoreId);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn(['name' => 'category']);
        $categoryMock = $this->createMock(CategoryModel::class);
        $categoryMock->method('getId')->willReturn($categoryId);
        $categoryMock->method('hasData')->willReturnCallback(fn ($key) => array_key_exists($key, $data));
        $categoryMock->method('getData')->willReturnCallback(fn ($key) => $data[$key] ?? null);
        $loadedMock = $this->createMock(CategoryModel::class);
        $loadedMock->method('getId')->willReturn($categoryId);
        $loadedMock->expects($this->once())->method('setStoreId')->with($expectedStoreId);
        $loadedMock->expects($this->once())->method('load')->with($categoryId);
        $loadedMock->expects($this->once())->method('validate')->willReturn(true);
        $reloadedMock = $this->createMock(CategoryModel::class);
        $reloadedMock->method('getId')->willReturn($categoryId);
        $reloadedMock->expects($this->once())->method('setStoreId')->with($expectedStoreId);
        $reloadedMock->expects($this->once())->method('load')->with($categoryId);
        $this->categoryFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($loadedMock, $reloadedMock);
        $this->populateWithValuesMock->expects($this->once())
            ->method('execute')
            ->with($loadedMock, ['name' => 'category', 'store_id' => $expectedStoreId, 'entity_id' => null]);
        $this->categoryResourceMock->expects($this->once())->method('save')->with($loadedMock);

        $this->assertSame($reloadedMock, $this->model->save($categoryMock));
    }

    public function testCreateNewCategoryInExplicitStore(): void
    {
        $parentCategoryId = 15;
        $newCategoryId = 25;
        $explicitStoreId = 3;
        $this->storeMock->method('getId')->willReturn(1);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn(['name' => 'category', 'level' => '1', 'path' => '1/2', 'parent_id' => 1]);
        $categoryMock = $this->createMock(CategoryModel::class);
        $categoryMock->method('hasData')->willReturnCallback(fn ($key) => $key === 'store_id');
        $categoryMock->method('getData')
            ->willReturnCallback(fn ($key) => $key === 'store_id' ? $explicitStoreId : null);
        $categoryMock->method('getParentId')->willReturn($parentCategoryId);
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $savedCount = 0;
        $this->categoryResourceMock->expects($this->once())
            ->method('save')
            ->with($categoryMock)
            ->willReturnCallback(function () use (&$savedCount) {
                $savedCount++;
                return DataObject::class;
            });
        $categoryMock->method('getId')
            ->willReturnCallback(fn () => $savedCount > 0 ? $newCategoryId : null);
        $parentMock = $this->createMock(CategoryModel::class);
        $parentMock->method('getId')->willReturn($parentCategoryId);
        $parentMock->method('getPath')->willReturn('path');
        $parentMock->expects($this->once())->method('setStoreId')->with($explicitStoreId);
        $reloadedMock = $this->createMock(CategoryModel::class);
        $reloadedMock->method('getId')->willReturn($newCategoryId);
        $reloadedMock->expects($this->once())->method('setStoreId')->with($explicitStoreId);
        $this->categoryFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($parentMock, $reloadedMock);
        $this->populateWithValuesMock->expects($this->once())
            ->method('execute')
            ->with(
                $categoryMock,
                ['name' => 'category', 'store_id' => $explicitStoreId, 'path' => 'path',
                    'parent_id' => $parentCategoryId, 'level' => null]
            );

        $this->assertSame($reloadedMock, $this->model->save($categoryMock));
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

    #[DataProvider('saveWithValidateCategoryExceptionDataProvider')]
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

        $objectMock = $this->createPartialMock(DataObject::class, []);

        $categoryMock->expects(
            $this->atLeastOnce()
        )->method('getId')->willReturn($categoryId);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $objectMock->setFrontend($objectMock);
        $objectMock->setLabel('ValidateCategoryTest');
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
        $categoryMock->method('getId')->willReturn(
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
