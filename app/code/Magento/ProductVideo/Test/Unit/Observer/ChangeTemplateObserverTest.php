<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Unit\Observer;

class ChangeTemplateObserverTest extends \PHPUnit_Framework_TestCase
{
    public function testChangeTemplate()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer $observer */
        $observer = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject
         * |\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo $block
         */
        $block = $this->getMock(\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo::class, [], [], '', false);
        $block->expects($this->once())
            ->method('setTemplate')
            ->with('Magento_ProductVideo::helper/gallery.phtml')
            ->willReturnSelf();
        $observer->expects($this->once())->method('__call')->with('getBlock')->willReturn($block);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ProductVideo\Observer\ChangeTemplateObserver $unit */
        $unit = $this->getMock(\Magento\ProductVideo\Observer\ChangeTemplateObserver::class, null, [], '', false);
        $unit->execute($observer);
    }
}
