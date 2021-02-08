<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UnsetAllObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Observer\UnsetAllObserver */
    protected $object;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $checkoutSession;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->object = $this->objectManager->getObject(
            \Magento\Checkout\Observer\UnsetAllObserver::class,
            ['checkoutSession' => $this->checkoutSession]
        );
    }

    public function testUnsetAll()
    {
        $this->checkoutSession->expects($this->once())->method('clearQuote')->willReturnSelf();
        $this->checkoutSession->expects($this->once())->method('clearStorage')->willReturnSelf();

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->execute($observerMock);
    }
}
