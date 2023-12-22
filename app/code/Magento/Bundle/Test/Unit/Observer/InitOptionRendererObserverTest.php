<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Observer;

use Magento\Bundle\Observer\InitOptionRendererObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Options;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Observer\InitOptionRendererObserver
 */
class InitOptionRendererObserverTest extends TestCase
{
    /**
     * @var Options|MockObject
     */
    private $blockMock;

    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var InitOptionRendererObserver
     */
    private $observer;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getBlock'])
            ->getMock();

        $this->blockMock = $this->getMockBuilder(Options::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addOptionsRenderCfg'])
            ->getMock();

        $this->observer = $this->objectManager->getObject(InitOptionRendererObserver::class);
    }

    /**
     * Test observer execute method
     */
    public function testProductOptionRendererInit()
    {
        $this->observerMock
            ->expects($this->once())
            ->method('getBlock')
            ->willReturn($this->blockMock);

        $this->blockMock
            ->expects($this->once())
            ->method('addOptionsRenderCfg')
            ->willReturn($this->blockMock);

        $this->observer->execute($this->observerMock);
    }
}
