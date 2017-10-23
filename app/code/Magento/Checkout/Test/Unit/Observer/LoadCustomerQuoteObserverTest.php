<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LoadCustomerQuoteObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Observer\LoadCustomerQuoteObserver */
    protected $object;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSession;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->object = $this->objectManager->getObject(
            \Magento\Checkout\Observer\LoadCustomerQuoteObserver::class,
            [
                'checkoutSession' => $this->checkoutSession,
                'messageManager' => $this->messageManager
            ]
        );
    }

    public function testLoadCustomerQuoteThrowingCoreException()
    {
        $this->checkoutSession->expects($this->once())->method('loadCustomerQuote')->willThrowException(
            new \Magento\Framework\Exception\LocalizedException(__('Message'))
        );
        $this->messageManager->expects($this->once())->method('addError')->with('Message');

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->execute($observerMock);
    }

    public function testLoadCustomerQuoteThrowingException()
    {
        $exception = new \Exception('Message');
        $this->checkoutSession->expects($this->once())->method('loadCustomerQuote')->will(
            $this->throwException($exception)
        );
        $this->messageManager->expects($this->once())->method('addException')
            ->with($exception, 'Load customer quote error');

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->execute($observerMock);
    }
}
