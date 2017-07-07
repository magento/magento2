<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\View;

class TabsTest extends \PHPUnit_Framework_TestCase
{
    public function testAddTab()
    {
        $tabBlock = $this->getMock(\Magento\Framework\View\Element\Template::class, [], [], '', false);
        $tabBlock->expects($this->once())->method('setTemplate')->with('template')->will($this->returnSelf());

        $layout = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);
        $layout->expects($this->once())->method('createBlock')->with('block')->will($this->returnValue($tabBlock));

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $block = $helper->getObject(\Magento\Catalog\Block\Product\View\Tabs::class, ['layout' => $layout]);
        $block->addTab('alias', 'title', 'block', 'template', 'header');

        $expectedTabs = [['alias' => 'alias', 'title' => 'title', 'header' => 'header']];
        $this->assertEquals($expectedTabs, $block->getTabs());
    }
}
