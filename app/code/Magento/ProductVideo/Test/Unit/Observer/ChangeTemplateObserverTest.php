<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Unit\Observer;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ChangeTemplateObserverTest extends \PHPUnit\Framework\TestCase
{
    public function testChangeTemplate()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event\Observer $observer */
        $observer = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getBlock']);

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject
         * |\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo $block
         */
        $block = $this->createMock(\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo::class);
        $block->expects($this->once())
            ->method('setTemplate')
            ->with('Magento_ProductVideo::helper/gallery.phtml')
            ->willReturnSelf();
        $observer->expects($this->once())->method('getBlock')->willReturn($block);

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\ProductVideo\Observer\ChangeTemplateObserver $unit */
        $this->objectManager = new ObjectManager($this);
        $unit = $this->objectManager->getObject(\Magento\ProductVideo\Observer\ChangeTemplateObserver::class);
        $unit->execute($observer);
    }
}
