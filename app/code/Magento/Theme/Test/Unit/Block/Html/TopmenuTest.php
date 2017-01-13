<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Theme\Block\Html\Topmenu;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Data\Tree\NodeFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TopmenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var NodeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $nodeFactory;

    /**
     * @var TreeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $treeFactory;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $category;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();

        $this->nodeFactory = $this->getMockBuilder(\Magento\Framework\Data\Tree\NodeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->treeFactory = $this->getMockBuilder(\Magento\Framework\Data\TreeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            [
                'urlBuilder' => $this->urlBuilder,
                'storeManager' => $this->storeManager,
                'eventManager' => $this->eventManagerMock,
                'request' => $this->requestMock,
            ]
        );
    }

    protected function getTopmenu()
    {
        return new Topmenu($this->context, $this->nodeFactory, $this->treeFactory);
    }

    public function testGetHtmlWithoutSelectedCategory()
    {
        $topmenuBlock = $this->getTopmenu();

        $treeNode = $this->buildTree(false);

        $transportObject = new \Magento\Framework\DataObject(['html' => $this->htmlWithoutCategory]);

        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnMap([
                [
                    'page_block_html_topmenu_gethtml_before',
                    [
                        'menu' => $treeNode,
                        'block' => $topmenuBlock,
                        'request' => $this->requestMock,
                    ],
                    $this->eventManagerMock
                ],
                [
                    'page_block_html_topmenu_gethtml_after',
                    [
                        'menu' => $treeNode,
                        'transportObject' => $transportObject,
                    ],
                    $this->eventManagerMock
                ],
            ]);

        $this->assertEquals($this->htmlWithoutCategory, $topmenuBlock->getHtml());
    }

    public function testGetHtmlWithSelectedCategory()
    {
        $topmenuBlock = $this->getTopmenu();

        $treeNode = $this->buildTree(true);

        $transportObject = new \Magento\Framework\DataObject(['html' => $this->htmlWithCategory]);

        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnMap([
                [
                    'page_block_html_topmenu_gethtml_before',
                    [
                        'menu' => $treeNode,
                        'block' => $topmenuBlock,
                        'request' => $this->requestMock,
                    ],
                    $this->eventManagerMock
                ],
                [
                    'page_block_html_topmenu_gethtml_after',
                    [
                        'menu' => $treeNode,
                        'transportObject' => $transportObject,
                    ],
                    $this->eventManagerMock
                ],
            ]);

        $this->assertEquals($this->htmlWithCategory, $topmenuBlock->getHtml());
    }

    public function testGetCacheKeyInfo()
    {
        $nodeFactory = $this->getMock(\Magento\Framework\Data\Tree\NodeFactory::class, [], [], '', false);
        $treeFactory = $this->getMock(\Magento\Framework\Data\TreeFactory::class, [], [], '', false);

        $topmenu =  new Topmenu($this->context, $nodeFactory, $treeFactory);
        $this->urlBuilder->expects($this->once())->method('getUrl')->with('*/*/*')->willReturn('123');
        $this->urlBuilder->expects($this->once())->method('getBaseUrl')->willReturn('baseUrl');
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMock();
        $store->expects($this->once())->method('getCode')->willReturn('321');
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $this->assertEquals(
            ['BLOCK_TPL', '321', null, 'base_url' => 'baseUrl', 'template' => null, '123'],
            $topmenu->getCacheKeyInfo()
        );
    }

    /**
     * @param bool $isCurrentItem
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildTree($isCurrentItem)
    {
        $treeMock = $this->getMockBuilder(\Magento\Framework\Data\Tree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMock(\Magento\Catalog\Model\ResourceModel\Category\Tree::class, [], [], '', false);

        $children = $this->getMock(
            \Magento\Framework\Data\Tree\Node\Collection::class,
            ['count'],
            ['container' => $container]
        );

        for ($i = 0; $i < 10; $i++) {
            $id = "category-node-$i";
            $categoryNode = $this->getMock(
                \Magento\Framework\Data\Tree\Node::class,
                ['getId', 'hasChildren'],
                [],
                '',
                false
            );
            $categoryNode->expects($this->once())->method('getId')->willReturn($id);
            $categoryNode->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
            $categoryNode->setData(
                [
                    'name' => "Category $i",
                    'id' => $id,
                    'url' => "http://magento2/category-$i.html",
                    'is_active' => $i == 0 ? $isCurrentItem : false,
                    'is_current_item' => $i == 0 ? $isCurrentItem : false,

                ]
            );
            $children->add($categoryNode);
        }

        $children->expects($this->once())->method('count')->willReturn(10);

        $nodeMock = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChildren'])
            ->getMock();
        $nodeMock->expects($this->once())
            ->method('getChildren')
            ->willReturn($children);
        $nodeMock->expects($this->any())
            ->method('__call')
            ->with('getLevel', [])
            ->willReturn(null);

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

    public function testGetMenu()
    {
        $treeMock = $this->getMockBuilder(\Magento\Framework\Data\Tree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeMockData = [
            'data' => [],
            'idField' => 'root',
            'tree' => $treeMock,
        ];

        $nodeMock = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
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
