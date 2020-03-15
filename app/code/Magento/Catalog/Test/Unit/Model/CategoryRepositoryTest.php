<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
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
    private const STUB_CATEGORY_ID = 5;
    const STUB_STORE_ID = 1;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var MockObject|CategoryFactory
     */
    private $categoryFactoryMock;

    /**
     * @var MockObject|CategoryResourceModel
     */
    private $categoryResourceModelMock;

    /**
     * @var MockObject|ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverterMock;

    /**
     * @var MockObject|StoreInterface
     */
    private $storeMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    protected function setUp()
    {
        $this->categoryFactoryMock = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->categoryResourceModelMock = $this->createPartialMock(
            CategoryResourceModel::class,
            ['load', 'delete', 'save', 'getAttribute']
        );
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->extensibleDataObjectConverterMock = $this
            ->getMockBuilder(ExtensibleDataObjectConverter::class)
            ->setMethods(['toNestedArray'])
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

        $this->categoryRepository = (new ObjectManager($this))->getObject(
            CategoryRepository::class,
            [
                'categoryFactory' => $this->categoryFactoryMock,
                'categoryResourceModel' => $this->categoryResourceModelMock,
                'storeManager' => $this->storeManagerMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
                'metadataPool' => $this->metadataPoolMock,
            ]
        );
    }

    public function testGet()
    {
        $categoryId = 5;
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);

        $this->categoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryMock);

        $this->categoryResourceModelMock->expects($this->once())
            ->method('load')
            ->with($categoryMock, $categoryId);

        $this->assertEquals($categoryMock, $this->categoryRepository->get($categoryId));
    }

    public function testGetWhenCategoryDoesNotExist()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with id = 5');

        $categoryId = 5;
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->categoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryMock);

        $this->categoryResourceModelMock->expects($this->once())
            ->method('load')
            ->with($categoryMock, $categoryId);

        $this->assertEquals($categoryMock, $this->categoryRepository->get($categoryId));
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
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($categoryId);
        $this->categoryFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($categoryMock);
        $this->extensibleDataObjectConverterMock->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($categoryData));
        $categoryMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $categoryMock->expects($this->once())
            ->method('addData')
            ->with($dataForSave);
        $this->categoryResourceModelMock->expects($this->once())
            ->method('save')
            ->willReturn(DataObject::class);
        $this->assertEquals($categoryMock, $this->categoryRepository->save($categoryMock));
    }

    public function testCreateNewCategory()
    {
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $categoryId = null;
        $parentCategoryId = 15;
        $newCategoryId = 25;
        $categoryData = ['level' => '1', 'path' => '1/2', 'parent_id' => 1, 'name' => 'category'];
        $dataForSave = ['store_id' => 1, 'name' => 'category', 'path' => 'path', 'parent_id' => 15];

        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($categoryData);


        $categoryMock = $this->createMock(Category::class);

        $categoryMock->expects($this->any())->method('getId')
            ->will($this->onConsecutiveCalls($categoryId, $newCategoryId));
        $parentCategoryMock = $this->createMock(Category::class);
        $this->categoryFactoryMock->expects($this->exactly(2))->method('create')->willReturn($parentCategoryMock);
        $parentCategoryMock->expects($this->atLeastOnce())->method('getId')->willReturn($parentCategoryId);

        $categoryMock->expects($this->once())->method('getParentId')->willReturn($parentCategoryId);
        $parentCategoryMock->expects($this->once())->method('getPath')->willReturn('path');
        $categoryMock->expects($this->once())->method('addData')->with($dataForSave);
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $this->categoryResourceModelMock->expects($this->once())
            ->method('save')
            ->willReturn(DataObject::class);
        $this->assertEquals($categoryMock, $this->categoryRepository->save($categoryMock));
    }

    public function testSaveWithException()
    {
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save category');

        $categoryMock = $this->createMock(Category::class);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue([]));
        $categoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(self::STUB_CATEGORY_ID);
        $categoryMock->method('getStoreId')
            ->willReturn(self::STUB_STORE_ID);
        $this->categoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryMock);
        $categoryMock->expects($this->once())
            ->method('validate')
            ->willReturn([42 => 'Testing an exception.']);
        $this->categoryRepository->save($categoryMock);
    }

    /**
     * @dataProvider saveWithValidateCategoryExceptionDataProvider
     */
    public function testSaveWithValidateCategoryException($error, $expectedException, $expectedExceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $categoryMock = $this->createMock(Category::class);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue([]));
        $objectMock = $this->createPartialMock(DataObject::class, ['getFrontend', 'getLabel']);
        $categoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(self::STUB_CATEGORY_ID);
        $categoryMock->method('getStoreId')
            ->willReturn(self::STUB_STORE_ID);
        $this->categoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryMock);
        $objectMock->expects($this->any())
            ->method('getFrontend')
            ->willReturn($objectMock);
        $objectMock->expects($this->any())
            ->method('getLabel')
            ->willReturn('ValidateCategoryTest');
        $categoryMock->expects($this->once())
            ->method('validate')
            ->willReturn([42 => $error]);
        $this->categoryResourceModelMock->expects($this->any())
            ->method('getAttribute')
            ->with(42)
            ->willReturn($objectMock);
        $categoryMock->expects($this->never())
            ->method('unsetData');
        $this->categoryRepository->save($categoryMock);
    }

    /**
     * @return array
     */
    public function saveWithValidateCategoryExceptionDataProvider()
    {
        return [
            [
                true,
                \Magento\Framework\Exception\CouldNotSaveException::class,
                'Could not save category: The "ValidateCategoryTest" attribute is required. Enter and try again.'
            ],
            [
                'Something went wrong',
                \Magento\Framework\Exception\CouldNotSaveException::class,
                'Could not save category: Something went wrong'
            ]
        ];
    }

    public function testDelete()
    {
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')
            ->willReturn(self::STUB_CATEGORY_ID);
        $this->assertTrue($this->categoryRepository->delete($categoryMock));
    }

    public function testDeleteWithException()
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage('Cannot delete category with id');
        $categoryMock = $this->createMock(Category::class);
        $this->categoryResourceModelMock->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception());
        $this->categoryRepository->delete($categoryMock);
    }

    public function testDeleteByIdentifier()
    {
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CATEGORY_ID);
        $this->categoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryMock);
        $this->categoryResourceModelMock->expects($this->once())
            ->method('load')
            ->with($categoryMock, self::STUB_CATEGORY_ID);
        $this->categoryResourceModelMock->expects($this->once())
            ->method('delete')
            ->with($categoryMock);
        $this->assertTrue($this->categoryRepository->deleteByIdentifier(self::STUB_CATEGORY_ID));
    }

    public function testDeleteByIdentifierWithException()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with id = 5');

        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->categoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryMock);
        $this->categoryResourceModelMock->expects($this->once())
            ->method('load')
            ->with($categoryMock, self::STUB_CATEGORY_ID);

        $this->categoryRepository->deleteByIdentifier(self::STUB_CATEGORY_ID);
    }
}
