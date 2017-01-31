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

    // @codingStandardsIgnoreStart

    /** @var string  */
    protected $htmlWithoutCategory = <<<HTML
<li  class="level0 nav-1 first"><a href="http://magento2/category-0.html" ><span></span></a></li><li  class="level0 nav-2"><a href="http://magento2/category-1.html" ><span></span></a></li><li  class="level0 nav-3"><a href="http://magento2/category-2.html" ><span></span></a></li><li  class="level0 nav-4"><a href="http://magento2/category-3.html" ><span></span></a></li><li  class="level0 nav-5"><a href="http://magento2/category-4.html" ><span></span></a></li><li  class="level0 nav-6"><a href="http://magento2/category-5.html" ><span></span></a></li><li  class="level0 nav-7"><a href="http://magento2/category-6.html" ><span></span></a></li><li  class="level0 nav-8"><a href="http://magento2/category-7.html" ><span></span></a></li><li  class="level0 nav-9"><a href="http://magento2/category-8.html" ><span></span></a></li><li  class="level0 nav-10 last"><a href="http://magento2/category-9.html" ><span></span></a></li>
HTML;

    /** @var string  */
    protected $htmlWithCategory = <<<HTML
<li  class="level0 nav-1 first active"><a href="http://magento2/category-0.html" ><span></span></a></li><li  class="level0 nav-2"><a href="http://magento2/category-1.html" ><span></span></a></li><li  class="level0 nav-3"><a href="http://magento2/category-2.html" ><span></span></a></li><li  class="level0 nav-4"><a href="http://magento2/category-3.html" ><span></span></a></li><li  class="level0 nav-5"><a href="http://magento2/category-4.html" ><span></span></a></li><li  class="level0 nav-6"><a href="http://magento2/category-5.html" ><span></span></a></li><li  class="level0 nav-7"><a href="http://magento2/category-6.html" ><span></span></a></li><li  class="level0 nav-8"><a href="http://magento2/category-7.html" ><span></span></a></li><li  class="level0 nav-9"><a href="http://magento2/category-8.html" ><span></span></a></li><li  class="level0 nav-10 last"><a href="http://magento2/category-9.html" ><span></span></a></li>
HTML;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    // @codingStandardsIgnoreEnd

    protected function setUp()
    {

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();
        $this->context = $objectManager->getObject(
            'Magento\Framework\View\Element\Template\Context',
            ['urlBuilder' => $this->urlBuilder, 'storeManager' => $this->storeManager]
        );

    }

    protected function getTopmenu()
    {
        return new Topmenu($this->context, $this->nodeFactory, $this->treeFactory);
    }

    public function testGetHtmlWithoutSelectedCategory()
    {
        $this->buildTree(false);
        $this->assertEquals($this->htmlWithoutCategory, $this->getTopmenu()->getHtml());
    }

    public function testGetHtmlWithSelectedCategory()
    {
        $this->buildTree(true);
        $this->assertEquals($this->htmlWithCategory, $this->getTopmenu()->getHtml());
    }

    public function testGetCacheKeyInfo()
    {
        $nodeFactory = $this->getMock('Magento\Framework\Data\Tree\NodeFactory', [], [], '', false);
        $treeFactory = $this->getMock('Magento\Framework\Data\TreeFactory', [], [], '', false);

        $topmenu =  new Topmenu($this->context, $nodeFactory, $treeFactory);
        $this->urlBuilder->expects($this->once())->method('getUrl')->with('*/*/*')->willReturn('123');
        $this->urlBuilder->expects($this->once())->method('getBaseUrl')->willReturn('baseUrl');
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
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
     * @param $isCurrentItem
     * @return void
     */
    private function buildTree($isCurrentItem)
    {
        $this->nodeFactory = $this->getMock('Magento\Framework\Data\Tree\NodeFactory', [], [], '', false);
        $this->treeFactory = $this->getMock('Magento\Framework\Data\TreeFactory', [], [], '', false);

        $tree = $this->getMock('Magento\Framework\Data\Tree', [], [], '', false);

        $container = $this->getMock('Magento\Catalog\Model\ResourceModel\Category\Tree', [], [], '', false);

        $children = $this->getMock(
            'Magento\Framework\Data\Tree\Node\Collection',
            ['count'],
            ['container' => $container]
        );

        for ($i = 0; $i < 10; $i++) {
            $id = "category-node-$i";
            $categoryNode = $this->getMock('Magento\Framework\Data\Tree\Node', ['getId', 'hasChildren'], [], '', false);
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

        $node = $this->getMock('Magento\Framework\Data\Tree\Node', ['getChildren'], [], '', false);
        $node->expects($this->once())->method('getChildren')->willReturn($children);
        $node->expects($this->any())->method('__call')->with('getLevel', [])->willReturn(null);

        $this->nodeFactory->expects($this->once())->method('create')->willReturn($node);

        $this->treeFactory->expects($this->once())->method('create')->willReturn($tree);
    }
}
