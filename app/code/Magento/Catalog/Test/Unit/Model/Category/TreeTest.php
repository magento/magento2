<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category;

use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TreeTest extends TestCase
{
    /**
     * @var MockObject|Tree
     */
    protected $categoryTreeMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var MockObject|Collection
     */
    protected $categoryCollection;

    /**
     * @var MockObject|CategoryTreeInterfaceFactory
     */
    protected $treeFactoryMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Category\Tree
     */
    protected $tree;

    /**
     * @var \Magento\Catalog\Model\Category\Tree
     */
    protected $node;

    /**
     * @var TreeFactory
     */
    private $treeResourceFactoryMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->categoryTreeMock = $this->getMockBuilder(
            Tree::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->categoryCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(
            StoreManagerInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->treeResourceFactoryMock = $this->createMock(
            TreeFactory::class
        );
        $this->treeResourceFactoryMock->method('create')
            ->willReturn($this->categoryTreeMock);

        $methods = ['create'];
        $this->treeFactoryMock =
            $this->createPartialMock(CategoryTreeInterfaceFactory::class, $methods);

        $this->tree = $this->objectManager
            ->getObject(
                \Magento\Catalog\Model\Category\Tree::class,
                [
                    'categoryCollection' => $this->categoryCollection,
                    'categoryTree' => $this->categoryTreeMock,
                    'storeManager' => $this->storeManagerMock,
                    'treeFactory' => $this->treeFactoryMock,
                    'treeResourceFactory' => $this->treeResourceFactoryMock,
                ]
            );
    }

    public function testGetNode()
    {
        $category = $this->getMockBuilder(
            Category::class
        )->disableOriginalConstructor()
            ->getMock();
        $category->expects($this->exactly(2))->method('getId')->willReturn(1);

        $node = $this->getMockBuilder(
            Node::class
        )->disableOriginalConstructor()
            ->getMock();

        $node->expects($this->once())->method('loadChildren');
        $this->categoryTreeMock->expects($this->once())->method('loadNode')
            ->with(1)
            ->willReturn($node);

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($store);

        $this->categoryCollection->expects($this->any())->method('addAttributeToSelect')->willReturnSelf();
        $this->categoryCollection->expects($this->once())->method('setProductStoreId')->willReturnSelf();
        $this->categoryCollection->expects($this->once())->method('setLoadProductCount')->willReturnSelf();
        $this->categoryCollection->expects($this->once())->method('setStoreId')->willReturnSelf();

        $this->categoryTreeMock->expects($this->once())->method('addCollectionData')
            ->with($this->categoryCollection);
        $this->tree->getRootNode($category);
    }

    public function testGetRootNode()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())->method('getRootCategoryId')->willReturn(2);
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->categoryCollection->expects($this->any())->method('addAttributeToSelect')->willReturnSelf();
        $this->categoryCollection->expects($this->once())->method('setProductStoreId')->willReturnSelf();
        $this->categoryCollection->expects($this->once())->method('setLoadProductCount')->willReturnSelf();
        $this->categoryCollection->expects($this->once())->method('setStoreId')->willReturnSelf();

        $node = $this->getMockBuilder(
            Tree::class
        )->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->once())->method('addCollectionData')
            ->with($this->categoryCollection);
        $node->expects($this->once())->method('getNodeById')->with(2);
        $this->categoryTreeMock->expects($this->once())->method('load')
            ->with(null)
            ->willReturn($node);
        $this->tree->getRootNode();
    }

    public function testGetTree()
    {
        $depth = 2;
        $currentLevel = 1;

        $treeNodeMock1 = $this->getMockForAbstractClass(CategoryTreeInterface::class);
        $treeNodeMock1->expects($this->once())->method('setId')->with($currentLevel)->willReturnSelf();
        $treeNodeMock1->expects($this->once())->method('setParentId')->with($currentLevel - 1)->willReturnSelf();
        $treeNodeMock1->expects($this->once())->method('setName')->with('Name' . $currentLevel)->willReturnSelf();
        $treeNodeMock1->expects($this->once())->method('setPosition')->with($currentLevel)->willReturnSelf();
        $treeNodeMock1->expects($this->once())->method('setLevel')->with($currentLevel)->willReturnSelf();
        $treeNodeMock1->expects($this->once())->method('setIsActive')->with(true)->willReturnSelf();
        $treeNodeMock1->expects($this->once())->method('setProductCount')->with(4)->willReturnSelf();
        $treeNodeMock1->expects($this->once())->method('setChildrenData')->willReturnSelf();

        $treeNodeMock2 = $this->getMockForAbstractClass(CategoryTreeInterface::class);
        $treeNodeMock2->expects($this->once())->method('setId')->with($currentLevel)->willReturnSelf();
        $treeNodeMock2->expects($this->once())->method('setParentId')->with($currentLevel - 1)->willReturnSelf();
        $treeNodeMock2->expects($this->once())->method('setName')->with('Name' . $currentLevel)->willReturnSelf();
        $treeNodeMock2->expects($this->once())->method('setPosition')->with($currentLevel)->willReturnSelf();
        $treeNodeMock2->expects($this->once())->method('setLevel')->with($currentLevel)->willReturnSelf();
        $treeNodeMock2->expects($this->once())->method('setIsActive')->with(true)->willReturnSelf();
        $treeNodeMock2->expects($this->once())->method('setProductCount')->with(4)->willReturnSelf();
        $treeNodeMock2->expects($this->once())->method('setChildrenData')->willReturnSelf();

        $this->treeFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($treeNodeMock1, $treeNodeMock2));
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getParentId',
                    'getPosition',
                    'getLevel',
                    'getProductCount',
                ]
            )
            ->onlyMethods(
                [
                    'hasChildren',
                    'getChildren',
                    'getId',
                    'getName',
                    'getIsActive'
                ]
            )
            ->getMock();
        $node->expects($this->any())->method('hasChildren')->willReturn(true);
        $node->expects($this->any())->method('getChildren')->willReturn([$node]);

        $node->expects($this->any())->method('getId')->willReturn($currentLevel);
        $node->expects($this->any())->method('getParentId')->willReturn($currentLevel - 1);
        $node->expects($this->any())->method('getName')->willReturn('Name' . $currentLevel);
        $node->expects($this->any())->method('getPosition')->willReturn($currentLevel);
        $node->expects($this->any())->method('getLevel')->willReturn($currentLevel);
        $node->expects($this->any())->method('getIsActive')->willReturn(true);
        $node->expects($this->any())->method('getProductCount')->willReturn(4);
        $this->tree->getTree($node, $depth, $currentLevel);
    }

    public function testGetTreeWhenChildrenAreNotExist()
    {
        $currentLevel = 1;
        $treeNodeMock = $this->getMockForAbstractClass(CategoryTreeInterface::class);
        $this->treeFactoryMock->expects($this->any())->method('create')->willReturn($treeNodeMock);
        $treeNodeMock->expects($this->once())->method('setId')->with($currentLevel)->willReturnSelf();
        $treeNodeMock->expects($this->once())->method('setParentId')->with($currentLevel - 1)->willReturnSelf();
        $treeNodeMock->expects($this->once())->method('setName')->with('Name' . $currentLevel)->willReturnSelf();
        $treeNodeMock->expects($this->once())->method('setPosition')->with($currentLevel)->willReturnSelf();
        $treeNodeMock->expects($this->once())->method('setLevel')->with($currentLevel)->willReturnSelf();
        $treeNodeMock->expects($this->once())->method('setIsActive')->with(true)->willReturnSelf();
        $treeNodeMock->expects($this->once())->method('setProductCount')->with(4)->willReturnSelf();
        $treeNodeMock->expects($this->once())->method('setChildrenData')->willReturnSelf();

        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getParentId',
                    'getPosition',
                    'getLevel',
                    'getProductCount'
                ]
            )
            ->onlyMethods(
                [
                    'hasChildren',
                    'getChildren',
                    'getId',
                    'getName',
                    'getIsActive'
                ]
            )
            ->getMock();
        $node->expects($this->any())->method('hasChildren')->willReturn(false);
        $node->expects($this->never())->method('getChildren');

        $node->expects($this->once())->method('getId')->willReturn($currentLevel);
        $node->expects($this->once())->method('getParentId')->willReturn($currentLevel - 1);
        $node->expects($this->once())->method('getName')->willReturn('Name' . $currentLevel);
        $node->expects($this->once())->method('getPosition')->willReturn($currentLevel);
        $node->expects($this->once())->method('getLevel')->willReturn($currentLevel);
        $node->expects($this->once())->method('getIsActive')->willReturn(true);
        $node->expects($this->once())->method('getProductCount')->willReturn(4);
        $this->tree->getTree($node);
    }
}
