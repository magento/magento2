<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckExpirePersistentQuoteObserverTest extends TestCase
{
    /**
     * @var CheckExpirePersistentQuoteObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject|RequestInterface
     */
    private $requestMock;

    /**
     * @var MockObject|Quote
     */
    private $quoteMock;

    /**
     * @var MockObject|CartRepositoryInterface
     */
    private $quoteRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->persistentHelperMock = $this->createMock(Data::class);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getControllerAction'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getRequestUri', 'getServer'])
            ->getMockForAbstractClass();
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $this->model = new CheckExpirePersistentQuoteObserver(
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
            ->addMethods(['getIsPersistent'])
            ->onlyMethods(['getCustomerIsGuest'])
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
     * @param InvokedCount $expireCounter
     * @param InvokedCount $dispatchCounter
     * @param InvokedCount $setCustomerIdCounter
     * @return void
     * @dataProvider requestDataProvider
     */
    public function testExecuteWhenPersistentIsEnabled(
        string $refererUri,
        string $requestUri,
        InvokedCount $expireCounter,
        InvokedCount $dispatchCounter,
        InvokedCount $setCustomerIdCounter
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
    public static function requestDataProvider()
    {
        return [
            [
                'refererUri'           => 'checkout',
                'requestUri'           => 'index',
                'expireCounter'        => self::never(),
                'dispatchCounter'      => self::never(),
                'setCustomerIdCounter' => self::never(),
            ],
            [
                'refererUri'           => 'checkout',
                'requestUri'           => 'checkout',
                'expireCounter'        => self::never(),
                'dispatchCounter'      => self::never(),
                'setCustomerIdCounter' => self::never(),
            ],
            [
                'refererUri'           => 'index',
                'requestUri'           => 'checkout',
                'expireCounter'        => self::never(),
                'dispatchCounter'      => self::never(),
                'setCustomerIdCounter' => self::never(),
            ],
            [
                'refererUri'           => 'index',
                'requestUri'           => 'index',
                'expireCounter'        => self::once(),
                'dispatchCounter'      => self::once(),
                'setCustomerIdCounter' => self::once(),
            ],
        ];
    }
}
