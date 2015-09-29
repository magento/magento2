<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Theme\Block\Html\Topmenu;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TopmenuTest extends \PHPUnit_Framework_TestCase
{
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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $htmlWithoutCategory;

    /**
     * @var string
     */
    protected $htmlWithCategory;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->htmlWithoutCategory = '<li  class="level0 nav-1 first">'
            . '<a href="http://magento2/category-0.html" ><span></span></a></li>'
            . '<li  class="level0 nav-2"><a href="http://magento2/category-1.html" ><span></span></a></li>'
            . '<li  class="level0 nav-3"><a href="http://magento2/category-2.html" ><span></span></a></li>'
            . '<li  class="level0 nav-4"><a href="http://magento2/category-3.html" ><span></span></a></li>'
            . '<li  class="level0 nav-5"><a href="http://magento2/category-4.html" ><span></span></a></li>'
            . '<li  class="level0 nav-6"><a href="http://magento2/category-5.html" ><span></span></a></li>'
            . '<li  class="level0 nav-7"><a href="http://magento2/category-6.html" ><span></span></a></li>'
            . '<li  class="level0 nav-8"><a href="http://magento2/category-7.html" ><span></span></a></li>'
            . '<li  class="level0 nav-9"><a href="http://magento2/category-8.html" ><span></span></a></li>'
            . '<li  class="level0 nav-10 last"><a href="http://magento2/category-9.html" ><span></span></a></li>';

        $this->htmlWithCategory = '<li  class="level0 nav-1 first active">'
            . '<a href="http://magento2/category-0.html" ><span></span></a></li>'
            . '<li  class="level0 nav-2"><a href="http://magento2/category-1.html" ><span></span></a></li>'
            . '<li  class="level0 nav-3"><a href="http://magento2/category-2.html" ><span></span></a></li>'
            . '<li  class="level0 nav-4"><a href="http://magento2/category-3.html" ><span></span></a></li>'
            . '<li  class="level0 nav-5"><a href="http://magento2/category-4.html" ><span></span></a></li>'
            . '<li  class="level0 nav-6"><a href="http://magento2/category-5.html" ><span></span></a></li>'
            . '<li  class="level0 nav-7"><a href="http://magento2/category-6.html" ><span></span></a></li>'
            . '<li  class="level0 nav-8"><a href="http://magento2/category-7.html" ><span></span></a></li>'
            . '<li  class="level0 nav-9"><a href="http://magento2/category-8.html" ><span></span></a></li>'
            . '<li  class="level0 nav-10 last"><a href="http://magento2/category-9.html" ><span></span></a></li>';

        $this->objectManager = new ObjectManager($this);

        $this->context = $this->objectManager->getObject('Magento\Framework\View\Element\Template\Context');

        $this->nodeFactory = $this->getMock('Magento\Framework\Data\Tree\NodeFactory', [], [], '', false);
        $this->treeFactory = $this->getMock('Magento\Framework\Data\TreeFactory', [], [], '', false);
    }

    /**
     * @return void
     */
    protected function settingsForGetHtmlTest()
    {
        $isCurrentItem = $this->getName() == 'testGetHtmlWithSelectedCategory';
        $tree = $this->getMock('Magento\Framework\Data\Tree', [], [], '', false);

        $container = $this->getMock('Magento\Catalog\Model\Resource\Category\Tree', [], [], '', false);

        $children = $this->getMock(
            'Magento\Framework\Data\Tree\Node\Collection',
            ['count'],
            ['container' => $container]
        );

        for ($i = 0; $i < 10; $i++) {
            $id = "category-node-$i";
            $categoryNode = $this->getMock('Magento\Framework\Data\Tree\Node', ['getId', 'hasChildren'], [], '', false);
            $categoryNode->expects($this->once())
                ->method('getId')
                ->willReturn($id);
            $categoryNode->expects($this->atLeastOnce())
                ->method('hasChildren')
                ->willReturn(false);
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

        $children->expects($this->once())
            ->method('count')
            ->willReturn(10);

        $node = $this->getMock('Magento\Framework\Data\Tree\Node', ['getChildren'], [], '', false);
        $node->expects($this->once())
            ->method('getChildren')
            ->willReturn($children);
        $node->expects($this->any())
            ->method('__call')
            ->with('getLevel', [])
            ->willReturn(null);

        $this->nodeFactory->expects($this->once())
            ->method('create')
            ->willReturn($node);

        $this->treeFactory->expects($this->once())
            ->method('create')
            ->willReturn($tree);
    }

    /**
     * @return Topmenu
     */
    protected function getTopmenu()
    {
        return $this->objectManager->getObject(
            'Magento\Theme\Block\Html\Topmenu',
            [
                'context' => $this->context,
                'nodeFactory' => $this->nodeFactory,
                'treeFactory' => $this->treeFactory
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetHtmlWithoutSelectedCategory()
    {
        $this->settingsForGetHtmlTest();
        $this->assertEquals($this->htmlWithoutCategory, $this->getTopmenu()->getHtml());
    }

    /**
     * @return void
     */
    public function testGetHtmlWithSelectedCategory()
    {
        $this->settingsForGetHtmlTest();
        $this->assertEquals($this->htmlWithCategory, $this->getTopmenu()->getHtml());
    }

    /**
     * @return void
     */
    public function testGetIdentitiesDefault()
    {
        $this->assertEquals(['catalog_top_menu'], $this->getTopmenu()->getIdentities());
    }

    /**
     * @return void
     */
    public function testGetAndSetIdentities()
    {
        $identity = 'test';
        $topMenu = $this->getTopmenu();
        $topMenu->addIdentity($identity);
        $this->assertEquals(['catalog_top_menu', $identity], $topMenu->getIdentities());
    }
}
