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

    // @codingStandardsIgnoreStart

    /** @var string  */
    protected $htmlWithoutCategory = <<<HTML
<li  class="level0 nav-1 first"><a href="http://magento2/category-0.html" ><span></span></a></li><li  class="level0 nav-2"><a href="http://magento2/category-1.html" ><span></span></a></li><li  class="level0 nav-3"><a href="http://magento2/category-2.html" ><span></span></a></li><li  class="level0 nav-4"><a href="http://magento2/category-3.html" ><span></span></a></li><li  class="level0 nav-5"><a href="http://magento2/category-4.html" ><span></span></a></li><li  class="level0 nav-6"><a href="http://magento2/category-5.html" ><span></span></a></li><li  class="level0 nav-7"><a href="http://magento2/category-6.html" ><span></span></a></li><li  class="level0 nav-8"><a href="http://magento2/category-7.html" ><span></span></a></li><li  class="level0 nav-9"><a href="http://magento2/category-8.html" ><span></span></a></li><li  class="level0 nav-10 last"><a href="http://magento2/category-9.html" ><span></span></a></li>
HTML;

    /** @var string  */
    protected $htmlWithCategory = <<<HTML
<li  class="level0 nav-1 first active"><a href="http://magento2/category-0.html" ><span></span></a></li><li  class="level0 nav-2"><a href="http://magento2/category-1.html" ><span></span></a></li><li  class="level0 nav-3"><a href="http://magento2/category-2.html" ><span></span></a></li><li  class="level0 nav-4"><a href="http://magento2/category-3.html" ><span></span></a></li><li  class="level0 nav-5"><a href="http://magento2/category-4.html" ><span></span></a></li><li  class="level0 nav-6"><a href="http://magento2/category-5.html" ><span></span></a></li><li  class="level0 nav-7"><a href="http://magento2/category-6.html" ><span></span></a></li><li  class="level0 nav-8"><a href="http://magento2/category-7.html" ><span></span></a></li><li  class="level0 nav-9"><a href="http://magento2/category-8.html" ><span></span></a></li><li  class="level0 nav-10 last"><a href="http://magento2/category-9.html" ><span></span></a></li>
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

        $transportObject = new DataObject(['html' => $this->htmlWithoutCategory]);

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

        $this->assertEquals($this->htmlWithoutCategory, $topmenuBlock->getHtml());
    }

    /**
     * @return void
     */
    public function testGetHtmlWithSelectedCategory(): void
    {
        $topmenuBlock = $this->getTopmenu();

        $treeNode = $this->buildTree(true);

        $transportObject = new DataObject(['html' => $this->htmlWithCategory]);

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

        $this->assertEquals($this->htmlWithCategory, $topmenuBlock->getHtml());
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
    private function buildTree($isCurrentItem): MockObject
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

        $nodeMock = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren', '__call'])
            ->getMock();
        $nodeMock->expects($this->once())
            ->method('getChildren')
            ->willReturn($children);
        $nodeMock
            ->method('__call')
            ->withConsecutive(['setOutermostClass'], [], ['getLevel', []])
            ->willReturnOnConsecutiveCalls(null, [], null);

        $nodeMockData = [
            'data' => [],
            'idField' => 'root',
            'tree' => $treeMock,
        ];

        $this->nodeFactory->expects($this->any())
            ->method('create')
            ->with($nodeMockData)
            ->willReturn($nodeMock);

        $this->treeFactory->expects($this->once())
            ->method('create')
            ->willReturn($treeMock);

        return $nodeMock;
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

        $nodeMock = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->nodeFactory->expects($this->any())
            ->method('create')
            ->with($nodeMockData)
            ->willReturn($nodeMock);

        $this->treeFactory->expects($this->once())
            ->method('create')
            ->willReturn($treeMock);

        $topmenuBlock = $this->getTopmenu();
        $this->assertEquals($nodeMock, $topmenuBlock->getMenu());
    }
}
