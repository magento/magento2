<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Customer\Wishlist;

class ItemsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetColumns()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $layout = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $block = $layout->addBlock(\Magento\Wishlist\Block\Customer\Wishlist\Items::class, 'test');
        $child = $this->getMock(
            \Magento\Wishlist\Block\Customer\Wishlist\Item\Column::class,
            ['isEnabled'],
            [$objectManager->get(\Magento\Framework\View\Element\Context::class)],
            '',
            false
        );
        $child->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $layout->addBlock($child, 'child', 'test');
        $expected = $child->getType();
        $columns = $block->getColumns();
        $this->assertNotEmpty($columns);
        foreach ($columns as $column) {
            $this->assertSame($expected, $column->getType());
        }
    }
}
