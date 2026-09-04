<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChildrenCategoriesProviderTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MockObject
     */
    protected $category;

    /**
     * @var MockObject
     */
    protected $select;

    /**
     * @var MockObject
     */
    protected $connection;

    /**
     * @var ChildrenCategoriesProvider
     */
    protected $childrenCategoriesProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->category = $this->createPartialMock(
            Category::class,
            [
                'getPath',
                'getResourceCollection',
                'getResource',
                'getLevel',
                '__wakeup',
                'isObjectNew'
            ]
        );
        $categoryCollection = $this->createPartialMockWithReflection(
            AbstractCollection::class,
            ['addAttributeToSelect', 'addIdFilter']
        );
        $this->category->method('getPath')->willReturn('category-path');
        $this->category->method('getResourceCollection')->willReturn($categoryCollection);
        $categoryCollection->method('addAttributeToSelect')->willReturnSelf();
        $categoryCollection->method('addIdFilter')->with(['id'])->willReturnSelf();
        $this->select = $this->createPartialMock(
            Select::class,
            ['where', 'deleteFromSelect', 'from']
        );
        $this->connection = $this->createMock(AdapterInterface::class);
        $categoryResource = $this->createMock(CategoryResource::class);
        $this->category->method('getResource')->willReturn($categoryResource);
        $categoryResource->method('getConnection')->willReturn($this->connection);
        $this->connection->method('select')->willReturn($this->select);
        $this->connection->method('quoteIdentifier')->willReturnArgument(0);
        $this->select->method('from')->willReturnSelf();

        $this->childrenCategoriesProvider = (new ObjectManager($this))->getObject(
            ChildrenCategoriesProvider::class
        );
    }

    /**
     * @return void
     */
    public function testGetChildrenRecursive(): void
    {
        $bind = ['c_path' => 'category-path/%'];
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->select->method('where')->with('path LIKE :c_path')->willReturnSelf();
        $this->connection->method('fetchCol')->with($this->select, $bind)->willReturn(['id']);
        $this->childrenCategoriesProvider->getChildren($this->category, true);
    }

    /**
     * @return void
     */
    public function testGetChildrenForNewCategory(): void
    {
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(true);
        $this->assertEquals([], $this->childrenCategoriesProvider->getChildren($this->category));
    }

    /**
     * @return void
     */
    public function testGetChildren(): void
    {
        $categoryLevel = 3;
        $this->select
            ->method('where')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['path LIKE :c_path'] => $this->select,
                ['level <= :c_level'] => $this->select
            });
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->category->expects($this->once())->method('getLevel')->willReturn($categoryLevel);
        $bind = ['c_path' => 'category-path/%', 'c_level' => $categoryLevel + 1];
        $this->connection->method('fetchCol')->with($this->select, $bind)->willReturn(['id']);

        $this->childrenCategoriesProvider->getChildren($this->category, false);
    }
}
