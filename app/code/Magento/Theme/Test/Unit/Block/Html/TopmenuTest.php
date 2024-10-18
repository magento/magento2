<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Tree as CategoryTree;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Topmenu;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Item as MenuItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TopmenuTest extends TestCase
{
    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var NodeFactory|MockObject
     */
    protected $nodeFactory;

    /**
     * @var TreeFactory|MockObject
     */
    protected $treeFactory;

    /**
     * @var Category|MockObject
     */
    protected $category;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Menu
     */
    private $menuMock;

    /**
     * @var MenuItem
     */
    private $menuItemMock;

    /**
     * @var Node
     */
    private $nodeMock;

    // @codingStandardsIgnoreStart
    /** @var string  */
    private $navigationMenuHtml = <<<HTML
<li  class="level0 nav-1 first"><a href="http://magento2/category-0.html" ><span></span></a></li><li  class="level0 nav-2"><a href="http://magento2/category-1.html" ><span></span></a></li><li  class="level0 nav-3"><a href="http://magento2/category-2.html" ><span></span></a></li><li  class="level0 nav-4"><a href="http://magento2/category-3.html" ><span></span></a></li><li  class="level0 nav-5"><a href="http://magento2/category-4.html" ><span></span></a></li><li  class="level0 nav-6"><a href="http://magento2/category-5.html" ><span></span></a></li><li  class="level0 nav-7"><a href="http://magento2/category-6.html" ><span></span></a></li><li  class="level0 nav-8"><a href="http://magento2/category-7.html" ><span></span></a></li><li  class="level0 nav-9"><a href="http://magento2/category-8.html" ><span></span></a></li><li  class="level0 nav-10 last"><a href="http://magento2/category-9.html" ><span></span></a></li>
HTML;
    // @codingStandardsIgnoreEnd

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->nodeFactory = $this->getMockBuilder(NodeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->treeFactory = $this->getMockBuilder(TreeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->menuMock = $this->getMockBuilder(Menu::class)
            ->onlyMethods(['count', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->menuItemMock = $this->getMockBuilder(MenuItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->nodeMock = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->addMethods(['getClass'])
            ->onlyMethods(['getChildren', 'hasChildren', '__call'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $this->urlBuilder,
                'storeManager' => $this->storeManager,
                'eventManager' => $this->eventManagerMock,
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * @return Topmenu
     */
    protected function getTopmenu(): Topmenu
    {
        return new Topmenu($this->context, $this->nodeFactory, $this->treeFactory);
    }

    /**
     * @return void
     */
    public function testGetHtmlWithoutSelectedCategory(): void
    {
        $topmenuBlock = $this->getTopmenu();

        $treeNode = $this->buildTree(false);

        $transportObject = new DataObject(['html' => $this->navigationMenuHtml]);

        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnMap([
                [
                    'page_block_html_topmenu_gethtml_before',
                    [
                        'menu' => $treeNode,
                        'block' => $topmenuBlock,
                        'request' => $this->requestMock
                    ],
                    $this->eventManagerMock
                ],
                [
                    'page_block_html_topmenu_gethtml_after',
                    [
                        'menu' => $treeNode,
                        'transportObject' => $transportObject
                    ],
                    $this->eventManagerMock
                ],
            ]);

        $this->assertEquals($this->navigationMenuHtml, $topmenuBlock->getHtml());
    }

    /**
     * @return void
     */
    public function testGetHtmlWithSelectedCategory(): void
    {
        $topmenuBlock = $this->getTopmenu();

        $treeNode = $this->buildTree(true);

        $transportObject = new DataObject(['html' => $this->navigationMenuHtml]);

        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnMap([
                [
                    'page_block_html_topmenu_gethtml_before',
                    [
                        'menu' => $treeNode,
                        'block' => $topmenuBlock,
                        'request' => $this->requestMock
                    ],
                    $this->eventManagerMock
                ],
                [
                    'page_block_html_topmenu_gethtml_after',
                    [
                        'menu' => $treeNode,
                        'transportObject' => $transportObject
                    ],
                    $this->eventManagerMock
                ],
            ]);

        $this->assertEquals($this->navigationMenuHtml, $topmenuBlock->getHtml());
    }

    /**
     * @return void
     */
    public function testGetCacheKeyInfo(): void
    {
        $nodeFactory = $this->createMock(NodeFactory::class);
        $treeFactory = $this->createMock(TreeFactory::class);

        $topmenu =  new Topmenu($this->context, $nodeFactory, $treeFactory);
        $this->urlBuilder->expects($this->once())->method('getBaseUrl')->willReturn('baseUrl');
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();
        $store->expects($this->once())->method('getCode')->willReturn('321');
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $this->assertEquals(
            ['BLOCK_TPL', '321', null, 'base_url' => 'baseUrl', 'template' => null],
            $topmenu->getCacheKeyInfo()
        );
    }

    /**
     * Create Tree Node mock object
     *
     * Helper method, that provides unified logic of creation of Tree Node mock objects.
     *
     * @param bool $isCurrentItem
     * @return MockObject
     */
    private function buildTree(bool $isCurrentItem): MockObject
    {
        $treeMock = $this->getMockBuilder(Tree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->createMock(CategoryTree::class);

        $children = $this->getMockBuilder(Collection::class)
            ->onlyMethods(['count'])
            ->setConstructorArgs(['container' => $container])
            ->getMock();

        for ($i = 0; $i < 10; $i++) {
            $id = "category-node-$i";
            $categoryNode = $this->createPartialMock(
                Node::class,
                ['getId', 'hasChildren']
            );
            $categoryNode->expects($this->once())->method('getId')->willReturn($id);
            $categoryNode->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
            $categoryNode->setData(
                [
                    'name' => "Category $i",
                    'id' => $id,
                    'url' => "http://magento2/category-$i.html",
                    'is_active' => $i == 0 ? $isCurrentItem : false,
                    'is_current_item' => $i == 0 ? $isCurrentItem : false

                ]
            );
            $children->add($categoryNode);
        }

        $children->expects($this->once())->method('count')->willReturn(10);

        $this->nodeMock->expects($this->once())
            ->method('getChildren')
            ->willReturn($children);
        $this->nodeMock
            ->method('__call')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == 'setOutermostClass') {
                    return null;
                } elseif ($arg1 == 'getLevel' && empty($arg2)) {
                    return null;
                }
                return [];
            });
        $nodeMockData = [
            'data' => [],
            'idField' => 'root',
            'tree' => $treeMock,
        ];

        $this->nodeFactory->expects($this->any())
            ->method('create')
            ->with($nodeMockData)
            ->willReturn($this->nodeMock);

        $this->treeFactory->expects($this->once())
            ->method('create')
            ->willReturn($treeMock);

        return $this->nodeMock;
    }

    /**
     * @return void
     */
    public function testGetMenu(): void
    {
        $treeMock = $this->getMockBuilder(Tree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeMockData = [
            'data' => [],
            'idField' => 'root',
            'tree' => $treeMock
        ];

        $this->nodeFactory->expects($this->any())
            ->method('create')
            ->with($nodeMockData)
            ->willReturn($this->nodeMock);

        $this->treeFactory->expects($this->once())
            ->method('create')
            ->willReturn($treeMock);

        $topmenuBlock = $this->getTopmenu();
        $this->assertEquals($this->nodeMock, $topmenuBlock->getMenu());
    }

    /**
     * Test counting items when there are no children.
     * @return void
     */
    public function testCountItemsNoChildren():void
    {
        $this->menuMock->expects($this->any())
            ->method('count')
            ->willReturn(5);
        $this->menuMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->menuItemMock]));

        $this->menuItemMock->expects($this->any())
            ->method('hasChildren')
            ->willReturn(false);

        $method = new \ReflectionMethod(
            Topmenu::class,
            '_countItems'
        );
        $method->setAccessible(true);

        $this->assertEquals(5, $method->invoke($this->getTopmenu(), $this->menuMock));
    }

    /**
     * Test counting items when there are children.
     * @return void
     */
    public function testCountItemsWithChildren(): void
    {
        // Setup child menu mock
        $childMenuMock = $this->createMock(Menu::class);
        $childMenuMock->expects($this->any())
            ->method('count')
            ->willReturn(3);
        $childMenuMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $this->menuItemMock->expects($this->any())
            ->method('hasChildren')
            ->willReturn(true);
        $this->menuItemMock->expects($this->any())
            ->method('getChildren')
            ->willReturn($childMenuMock);

        // Setup menu mock
        $this->menuMock->expects($this->any())
            ->method('count')
            ->willReturn(2);
        $this->menuMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->menuItemMock, $this->menuItemMock]));

        $method = new \ReflectionMethod(
            Topmenu::class,
            '_countItems'
        );
        $method->setAccessible(true);

        // Total should be 2 (top level) + 2 * 3 (children) = 8
        $this->assertEquals(8, $method->invoke($this->getTopmenu(), $this->menuMock));
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testColumnBrakeEmptyArray(): void
    {
        $this->testCountItemsNoChildren();

        $method = new \ReflectionMethod(
            Topmenu::class,
            '_columnBrake'
        );
        $method->setAccessible(true);

        $this->assertEquals([], $method->invoke($this->getTopmenu(), $this->menuMock, 5));
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testColumnBrakeWithoutItem(): void
    {
        $result = [
            [   'total' => 8,
                'max' => 2
            ],
            [
                'place' => 4,
                'colbrake' => false
            ],
            [
                'place' => 4,
                'colbrake' => false
            ]
        ];

        $this->testCountItemsWithChildren();

        $method = new \ReflectionMethod(
            Topmenu::class,
            '_columnBrake'
        );
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invoke($this->getTopmenu(), $this->menuMock, 2));
    }

    /**
     * @return void
     */
    public function testAddSubMenu(): void
    {
        $container = $this->createMock(CategoryTree::class);

        $children = $this->getMockBuilder(Collection::class)
            ->onlyMethods(['count'])
            ->setConstructorArgs(['container' => $container])
            ->getMock();

        $this->nodeMock->expects($this->atLeastOnce())
            ->method('hasChildren')
            ->willReturn(true);

        $this->nodeMock->expects($this->any())
            ->method('getChildren')
            ->willReturn($children);

        $method = new \ReflectionMethod(
            Topmenu::class,
            '_addSubMenu'
        );
        $method->setAccessible(true);

        $this->assertEquals(
            '<ul class="level0 "></ul>',
            $method->invoke($this->getTopmenu(), $this->nodeMock, 0, '', 2)
        );
    }

    /**
     * @return void
     */
    public function testSetCurrentClass(): void
    {
        $this->nodeMock->expects($this->once())
            ->method('getClass')
            ->willReturn(null);

        $method = new \ReflectionMethod(
            Topmenu::class,
            'setCurrentClass'
        );
        $method->setAccessible(true);

        $method->invoke($this->getTopmenu(), $this->nodeMock, '');
    }
}
