<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class CheckExpirePersistentQuoteObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
<<<<<<< HEAD
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    private $requestMock;

    /**
     * @inheritdoc
     */
=======
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\RequestInterface
     */
    private $requestMock;

>>>>>>> upstream/2.2-develop
    protected function setUp()
    {
        $this->sessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->persistentHelperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
<<<<<<< HEAD
        $this->observerMock = $this->createPartialMock(
            \Magento\Framework\Event\Observer::class,
            ['getControllerAction','__wakeUp']
        );
=======
        $this->observerMock
            = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getControllerAction',
            '__wakeUp']);
>>>>>>> upstream/2.2-develop
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequestUri', 'getServer'])
            ->getMockForAbstractClass();

        $this->model = new \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver(
            $this->sessionMock,
            $this->persistentHelperMock,
            $this->quoteManagerMock,
            $this->eventManagerMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock,
            $this->requestMock
        );
    }

    public function testExecuteWhenCanNotApplyPersistentData()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(false);
        $this->persistentHelperMock->expects($this->never())->method('isEnabled');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenPersistentIsNotEnabled()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->model->execute($this->observerMock);
    }

    /**
<<<<<<< HEAD
     * Test method \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver::execute when persistent is enabled.
     *
     * @param string $refererUri
     * @param string $requestUri
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $expireCounter
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $dispatchCounter
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $setCustomerIdCounter
     * @return void
     * @dataProvider requestDataProvider
     */
    public function testExecuteWhenPersistentIsEnabled(
        string $refererUri,
        string $requestUri,
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $expireCounter,
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $dispatchCounter,
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $setCustomerIdCounter
    ): void {
=======
     * Test method \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver::execute when persistent is enabled
     *
     * @param $refererUri
     * @param $requestUri
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expireCounter
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $dispatchCounter
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCustomerIdCounter
     * @dataProvider requestDataProvider
     */
    public function testExecuteWhenPersistentIsEnabled(
        $refererUri,
        $requestUri,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expireCounter,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $dispatchCounter,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCustomerIdCounter
    ) {
>>>>>>> upstream/2.2-develop
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
<<<<<<< HEAD
            ->willReturn(true);
        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->sessionMock->expects($this->once())->method('isPersistent')->willReturn(false);
        $this->customerSessionMock
            ->expects($this->atLeastOnce())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->checkoutSessionMock
            ->expects($this->atLeastOnce())
            ->method('getQuoteId')
            ->willReturn(10);
=======
            ->will($this->returnValue(true));
        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->customerSessionMock
            ->expects($this->atLeastOnce())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));
        $this->checkoutSessionMock
            ->expects($this->atLeastOnce())
            ->method('getQuoteId')
            ->will($this->returnValue(10));
>>>>>>> upstream/2.2-develop
        $this->eventManagerMock->expects($dispatchCounter)->method('dispatch');
        $this->quoteManagerMock->expects($expireCounter)->method('expire');
        $this->customerSessionMock
            ->expects($setCustomerIdCounter)
            ->method('setCustomerId')
            ->with(null)
<<<<<<< HEAD
            ->willReturnSelf();
=======
            ->will($this->returnSelf());
>>>>>>> upstream/2.2-develop
        $this->requestMock->expects($this->atLeastOnce())->method('getRequestUri')->willReturn($refererUri);
        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->willReturn($requestUri);
        $this->model->execute($this->observerMock);
    }

    /**
     * Request Data Provider
     *
     * @return array
     */
    public function requestDataProvider()
    {
        return [
            [
                'refererUri'           => 'checkout',
                'requestUri'           => 'index',
                'expireCounter'        => $this->never(),
                'dispatchCounter'      => $this->never(),
                'setCustomerIdCounter' => $this->never(),
            ],
            [
                'refererUri'           => 'checkout',
                'requestUri'           => 'checkout',
                'expireCounter'        => $this->never(),
                'dispatchCounter'      => $this->never(),
                'setCustomerIdCounter' => $this->never(),
            ],
            [
                'refererUri'           => 'index',
                'requestUri'           => 'checkout',
                'expireCounter'        => $this->never(),
                'dispatchCounter'      => $this->never(),
                'setCustomerIdCounter' => $this->never(),
            ],
            [
                'refererUri'           => 'index',
                'requestUri'           => 'index',
                'expireCounter'        => $this->once(),
                'dispatchCounter'      => $this->once(),
                'setCustomerIdCounter' => $this->once(),
            ],
        ];
    }
}
