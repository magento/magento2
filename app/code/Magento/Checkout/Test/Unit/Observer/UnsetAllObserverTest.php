<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Checkout\Observer\UnsetAllObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UnsetAllObserverTest extends TestCase
{
    /** @var UnsetAllObserver */
    protected $object;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var MockObject */
    protected $checkoutSession;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->createMock(Session::class);
        $this->object = $this->objectManager->getObject(
            UnsetAllObserver::class,
            ['checkoutSession' => $this->checkoutSession]
        );
    }

    public function testUnsetAll()
    {
        $this->checkoutSession->expects($this->once())->method('clearQuote')->willReturnSelf();
        $this->checkoutSession->expects($this->once())->method('clearStorage')->willReturnSelf();

        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->execute($observerMock);
    }
}
