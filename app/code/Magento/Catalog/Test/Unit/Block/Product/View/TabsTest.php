<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\View\Tabs;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\TestCase;

class TabsTest extends TestCase
{
    public function testAddTab()
    {
        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->with('template')->willReturnSelf();

        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())->method('createBlock')->with('block')->willReturn($tabBlock);

        $helper = new ObjectManager($this);
        $block = $helper->getObject(Tabs::class, ['layout' => $layout]);
        $block->addTab('alias', 'title', 'block', 'template', 'header');

        $expectedTabs = [['alias' => 'alias', 'title' => 'title', 'header' => 'header']];
        $this->assertEquals($expectedTabs, $block->getTabs());
    }
}
