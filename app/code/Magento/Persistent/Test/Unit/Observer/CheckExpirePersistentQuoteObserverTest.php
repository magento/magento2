<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckExpirePersistentQuoteObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\RequestInterface
     */
    private $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Quote
     */
    private $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CartRepositoryInterface
     */
    private $quoteRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->persistentHelperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->observerMock = $this->createPartialMock(
            \Magento\Framework\Event\Observer::class,
            ['getControllerAction','__wakeUp']
        );
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequestUri', 'getServer'])
            ->getMockForAbstractClass();
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $this->model = new \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver(
            $this->sessionMock,
            $this->persistentHelperMock,
            $this->quoteManagerMock,
            $this->eventManagerMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock,
            $this->requestMock,
            $this->quoteRepositoryMock
        );
        $this->quoteMock = $this->getMockBuilder(Quote::class)
        ->setMethods(['getCustomerIsGuest', 'getIsPersistent'])
        ->disableOriginalConstructor()
        ->getMock();
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
        $quoteId = 'quote_id_1';

        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->persistentHelperMock->expects($this->exactly(2))->method('isEnabled')->willReturn(false);
        $this->checkoutSessionMock->expects($this->exactly(2))->method('getQuoteId')->willReturn($quoteId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($quoteId)
            ->willThrowException(new NoSuchEntityException());
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->model->execute($this->observerMock);
    }

    /**
     * Test method \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver::execute when persistent is enabled.
     *
     * @param string $refererUri
     * @param string $requestUri
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $expireCounter
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $dispatchCounter
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $setCustomerIdCounter
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider requestDataProvider
     */
    public function testExecuteWhenPersistentIsEnabled(
        string $refererUri,
        string $requestUri,
        \PHPUnit\Framework\MockObject\Rule\InvokedCount $expireCounter,
        \PHPUnit\Framework\MockObject\Rule\InvokedCount $dispatchCounter,
        \PHPUnit\Framework\MockObject\Rule\InvokedCount $setCustomerIdCounter
    ): void {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->persistentHelperMock->expects($this->atLeastOnce())
            ->method('isEnabled')
            ->willReturn(true);
        $this->persistentHelperMock->expects($this->atLeastOnce())
            ->method('isShoppingCartPersist')
            ->willReturn(true);
        $this->sessionMock->expects($this->atLeastOnce())->method('isPersistent')->willReturn(false);
        $this->checkoutSessionMock
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->method('getCustomerIsGuest')->willReturn(true);
        $this->quoteMock->method('getIsPersistent')->willReturn(true);
        $this->customerSessionMock
            ->expects($this->atLeastOnce())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->checkoutSessionMock
            ->expects($this->atLeastOnce())
            ->method('getQuoteId')
            ->willReturn(10);
        $this->eventManagerMock->expects($dispatchCounter)->method('dispatch');
        $this->quoteManagerMock->expects($expireCounter)->method('expire');
        $this->customerSessionMock
            ->expects($setCustomerIdCounter)
            ->method('setCustomerId')
            ->with(null)
            ->willReturnSelf();
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
