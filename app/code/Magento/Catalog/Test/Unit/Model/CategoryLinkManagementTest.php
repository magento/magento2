<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryLinkManagement;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryLinkManagementTest extends TestCase
{
    /**
     * @var CategoryLinkManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $categoryRepositoryMock;

    /**
     * @var MockObject
     */
    protected $productLinkFactoryMock;

    protected function setUp(): void
    {
        $this->categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $productResource = $this->createMock(Product::class);
        $categoryLinkRepository = $this->getMockBuilder(CategoryLinkRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $indexerRegistry = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productLinkFactoryMock = $this->createPartialMock(
            CategoryProductLinkInterfaceFactory::class,
            ['create']
        );

        $this->model = new CategoryLinkManagement(
            $this->categoryRepositoryMock,
            $this->productLinkFactoryMock
        );

        $this->setProperties($this->model, [
            'productResource' => $productResource,
            'categoryLinkRepository' => $categoryLinkRepository,
            'productLinkFactory' => $this->productLinkFactoryMock,
            'indexerRegistry' => $indexerRegistry
        ]);
    }

    public function testGetAssignedProducts()
    {
        $categoryId = 42;
        $productId = 55;
        $position = 25;
        $productSku = 'testSku';
        $categoryProductLinkMock = $this->getMockForAbstractClass(CategoryProductLinkInterface::class);
        $categoryMock = $this->createMock(Category::class);
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $productMock->expects($this->once())->method('getData')->with('cat_index_position')->willReturn($position);
        $items = [$productId => $productMock];
        $productsMock = $this->createMock(Collection::class);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $categoryMock->expects($this->once())->method('getProductCollection')->willReturn($productsMock);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $productsMock->expects($this->once())->method('addFieldToSelect')->with('position')->willReturnSelf();
        $productsMock->expects($this->once())->method('groupByAttribute')->with('entity_id')->willReturnSelf();
        $productsMock->expects($this->once())->method('getItems')->willReturn($items);
        $productsMock->expects($this->once())
            ->method('getProductEntityMetadata')
            ->willReturn(new DataObject(['identifier_field' => 'entity_id']));
        $this->productLinkFactoryMock->expects($this->once())->method('create')->willReturn($categoryProductLinkMock);
        $categoryProductLinkMock->expects($this->once())
            ->method('setSku')
            ->with($productSku)
            ->willReturnSelf();
        $categoryProductLinkMock->expects($this->once())
            ->method('setPosition')
            ->with($position)
            ->willReturnSelf();
        $categoryProductLinkMock->expects($this->once())
            ->method('setCategoryId')
            ->with($categoryId)
            ->willReturnSelf();
        $this->assertEquals([$categoryProductLinkMock], $this->model->getAssignedProducts($categoryId));
    }

    /**
     * @param $object
     * @param array $properties
     */
    private function setProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }
}
