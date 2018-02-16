<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Button;

class SplitTest extends \PHPUnit_Framework_TestCase
{
    public function testHasSplit()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Backend\Block\Widget\Button\SplitButton $block */
        $block = $objectManagerHelper->getObject('Magento\Backend\Block\Widget\Button\SplitButton');
        $this->assertSame(true, $block->hasSplit());
        $block->setData('has_split', false);
        $this->assertSame(false, $block->hasSplit());
        $block->setData('has_split', true);
        $this->assertSame(true, $block->hasSplit());
    }
}
