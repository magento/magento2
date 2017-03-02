<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class CategoryManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\CategoryManagement
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepositoryMock;

    /**
     * @var \Magento\Catalog\Model\Category\Tree|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryTreeMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoriesFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var \Magento\Framework\App\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeMock;

    protected function setUp()
    {

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->categoryRepositoryMock = $this->getMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->categoryTreeMock = $this->getMock(\Magento\Catalog\Model\Category\Tree::class, [], [], '', false);
        $this->categoriesFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class,
            ['create', 'addFilter', 'getFirstItem'],
            [],
            '',
            false
        );

        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\CategoryManagement::class,
            [
                'categoryRepository' => $this->categoryRepositoryMock,
                'categoryTree' => $this->categoryTreeMock,
                'categoriesFactory' => $this->categoriesFactoryMock
            ]
        );

        $this->scopeResolverMock = $this->getMock(\Magento\Framework\App\ScopeResolverInterface::class);

        $this->scopeMock = $this->getMock(\Magento\Framework\App\ScopeInterface::class);

        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->model,
            'scopeResolver',
            $this->scopeResolverMock
        );
    }

    public function testGetTree()
    {
        $rootCategoryId = 1;
        $depth = 2;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $nodeMock = $this->getMock(\Magento\Framework\Data\Tree\Node::class, [], [], '', false);

        $this->categoryRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($rootCategoryId)
            ->willReturn($categoryMock);
        $this->categoryTreeMock
            ->expects($this->once())
            ->method('getRootNode')
            ->with($categoryMock)
            ->willReturn($nodeMock);
        $this->categoryTreeMock
            ->expects($this->once())
            ->method('getTree')
            ->with($nodeMock, $depth)
            ->willReturn('expected');
        $this->assertEquals(
            'expected',
            $this->model->getTree($rootCategoryId, $depth)
        );
    }

    public function testGetTreeWithNullArguments()
    {
        $rootCategoryId = null;
        $depth = null;
        $category = null;

        $this->categoryRepositoryMock->expects($this->never())->method('get');
        $this->categoryTreeMock->expects($this->once())->method('getRootNode')->with($category)->willReturn(null);
        $this->categoryTreeMock->expects($this->exactly(2))->method('getTree')->with($category, $depth);

        $this->scopeResolverMock
            ->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeMock);

        $this->scopeMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn(1);

        $this->assertEquals(
            $this->model->getTree($rootCategoryId, $depth),
            $this->categoryTreeMock->getTree(null, null)
        );
    }

    /**
     * Check is possible to get all categories for all store starting from top level root category
     */
    public function testGetTreeForAllScope()
    {
        $depth = null;
        $categoriesMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class,
            [],
            [],
            '',
            false
        );
        $categoryMock = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [],
            [],
            'categoryMock',
            false
        );
        $categoriesMock
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($categoryMock);
        $categoriesMock
            ->expects($this->once())
            ->method('addFilter')
            ->with('level', ['eq' => 0])
            ->willReturnSelf();
        $this->categoriesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($categoriesMock);
        $nodeMock = $this->getMock(
            \Magento\Framework\Data\Tree\Node::class,
            [],
            [],
            '',
            false
        );

        $this->categoryTreeMock
            ->expects($this->once())
            ->method('getTree')
            ->with($nodeMock, $depth);
        $this->categoryRepositoryMock
            ->expects($this->never())
            ->method('get');
        $this->categoryTreeMock
            ->expects($this->once())
            ->method('getRootNode')
            ->with($categoryMock)
            ->willReturn($nodeMock);

        $this->scopeResolverMock
            ->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeMock);

        $this->scopeMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn(\Magento\Store\Model\Store::ADMIN_CODE);

        $this->model->getTree();
    }

    public function testMove()
    {
        $categoryId = 2;
        $parentId = 1;
        $afterId = null;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], 'categoryMock', false);
        $parentCategoryMock = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [],
            [],
            'parentCategoryMock',
            false
        );

        $this->categoryRepositoryMock
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap(
                [
                    [$categoryId, null, $categoryMock],
                    [$parentId, null, $parentCategoryMock],
                ]
            ));
        $parentCategoryMock->expects($this->once())->method('hasChildren')->willReturn(true);
        $parentCategoryMock->expects($this->once())->method('getChildren')->willReturn('5,6,7');
        $categoryMock->expects($this->once())->method('getPath');
        $parentCategoryMock->expects($this->once())->method('getPath');
        $categoryMock->expects($this->once())->method('move')->with($parentId, '7');
        $this->assertTrue($this->model->move($categoryId, $parentId, $afterId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Operation do not allow to move a parent category to any of children category
     */
    public function testMoveWithException()
    {
        $categoryId = 2;
        $parentId = 1;
        $afterId = null;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], 'categoryMock', false);
        $parentCategoryMock = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [],
            [],
            'parentCategoryMock',
            false
        );

        $this->categoryRepositoryMock
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap(
                [
                    [$categoryId, null, $categoryMock],
                    [$parentId, null, $parentCategoryMock],
                ]
            ));
        $categoryMock->expects($this->once())->method('getPath')->willReturn('test');
        $parentCategoryMock->expects($this->once())->method('getPath')->willReturn('test');
        $this->model->move($categoryId, $parentId, $afterId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not move category
     */
    public function testMoveWithCouldNotMoveException()
    {
        $categoryId = 2;
        $parentId = 1;
        $afterId = null;
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], 'categoryMock', false);
        $parentCategoryMock = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [],
            [],
            'parentCategoryMock',
            false
        );

        $this->categoryRepositoryMock
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap(
                [
                    [$categoryId, null, $categoryMock],
                    [$parentId, null, $parentCategoryMock],
                ]
            ));
        $categoryMock->expects($this->once())
            ->method('move')
            ->with($parentId, $afterId)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('message')));
        $this->model->move($categoryId, $parentId, $afterId);
    }

    public function testGetCount()
    {
        $categoriesMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->categoriesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($categoriesMock);
        $categoriesMock
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('parent_id', ['gt' => 0])
            ->willReturnSelf();
        $categoriesMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount()
        );
    }
}
