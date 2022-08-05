<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductVideo\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo;
use Magento\ProductVideo\Observer\ChangeTemplateObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangeTemplateObserverTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function testChangeTemplate()
    {
        /** @var MockObject|Observer $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getBlock'])
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var MockObject|NewVideo $block
         */
        $block = $this->createMock(NewVideo::class);
        $block->expects($this->once())
            ->method('setTemplate')
            ->with('Magento_ProductVideo::helper/gallery.phtml')
            ->willReturnSelf();
        $observer->expects($this->once())->method('getBlock')->willReturn($block);

        /** @var MockObject|ChangeTemplateObserver $unit */
        $this->objectManager = new ObjectManager($this);
        $unit = $this->objectManager->getObject(ChangeTemplateObserver::class);
        $unit->execute($observer);
    }
}
