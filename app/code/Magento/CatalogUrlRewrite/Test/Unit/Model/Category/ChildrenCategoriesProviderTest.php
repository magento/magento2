<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChildrenCategoriesProviderTest extends TestCase
{
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
        $this->category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getPath',
                    'getResourceCollection',
                    'getResource',
                    'getLevel',
                    '__wakeup',
                    'isObjectNew'
                ]
            )
            ->getMock();
        $categoryCollection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addAttributeToSelect'])
            ->addMethods(['addIdFilter'])
            ->getMock();
        $this->category->expects($this->any())->method('getPath')->willReturn('category-path');
        $this->category->expects($this->any())->method('getResourceCollection')->willReturn($categoryCollection);
        $categoryCollection->expects($this->any())->method('addAttributeToSelect')->willReturnSelf();
        $categoryCollection->expects($this->any())->method('addIdFilter')->with(['id'])->willReturnSelf();
        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'deleteFromSelect', 'from'])
            ->getMock();
        $this->connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $categoryResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->category->expects($this->any())->method('getResource')->willReturn($categoryResource);
        $categoryResource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->connection->expects($this->any())->method('select')->willReturn($this->select);
        $this->connection->expects($this->any())->method('quoteIdentifier')->willReturnArgument(0);
        $this->select->expects($this->any())->method('from')->willReturnSelf();

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
        $this->select->expects($this->any())->method('where')->with('path LIKE :c_path')->willReturnSelf();
        $this->connection->expects($this->any())->method('fetchCol')->with($this->select, $bind)->willReturn(['id']);
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
            ->withConsecutive(['path LIKE :c_path'], ['level <= :c_level'])
            ->willReturnOnConsecutiveCalls($this->select, $this->select);
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->category->expects($this->once())->method('getLevel')->willReturn($categoryLevel);
        $bind = ['c_path' => 'category-path/%', 'c_level' => $categoryLevel + 1];
        $this->connection->expects($this->any())->method('fetchCol')->with($this->select, $bind)->willReturn(['id']);

        $this->childrenCategoriesProvider->getChildren($this->category, false);
    }
}
