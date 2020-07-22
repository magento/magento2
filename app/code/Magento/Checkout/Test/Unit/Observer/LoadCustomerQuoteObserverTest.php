<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Checkout\Observer\LoadCustomerQuoteObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoadCustomerQuoteObserverTest extends TestCase
{
    /** @var LoadCustomerQuoteObserver */
    protected $object;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var MockObject */
    protected $checkoutSession;

    /** @var ManagerInterface|MockObject */
    protected $messageManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->createMock(Session::class);
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->object = $this->objectManager->getObject(
            LoadCustomerQuoteObserver::class,
            [
                'checkoutSession' => $this->checkoutSession,
                'messageManager' => $this->messageManager
            ]
        );
    }

    public function testLoadCustomerQuoteThrowingCoreException()
    {
        $this->checkoutSession->expects($this->once())->method('loadCustomerQuote')->willThrowException(
            new LocalizedException(__('Message'))
        );
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with('Message');

        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->execute($observerMock);
    }

    public function testLoadCustomerQuoteThrowingException()
    {
        $exception = new \Exception('Message');
        $this->checkoutSession->expects($this->once())->method('loadCustomerQuote')->willThrowException(
            $exception
        );
        $this->messageManager->expects($this->once())->method('addExceptionMessage')
            ->with($exception, 'Load customer quote error');

        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->execute($observerMock);
    }
}
