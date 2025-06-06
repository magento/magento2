<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Model\QuoteResourceWrapper;
use Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver;
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
     * @var MockObject|QuoteResourceWrapper
     */
    private $quoteResourceWrapperMock;

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
        $this->quoteResourceWrapperMock = $this->createMock(QuoteResourceWrapper::class);

        $this->model = new CheckExpirePersistentQuoteObserver(
            $this->sessionMock,
            $this->persistentHelperMock,
            $this->quoteManagerMock,
            $this->eventManagerMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock,
            $this->requestMock,
            $this->quoteResourceWrapperMock
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
        $quoteId = 10;

        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->checkoutSessionMock->expects($this->any())->method('getQuoteId')->willReturn($quoteId);

        $this->quoteResourceWrapperMock->expects($this->once())
            ->method('isActive')
            ->with($quoteId)
            ->willReturn(true);
        $this->quoteResourceWrapperMock->expects($this->once())
            ->method('isPersistent')
            ->with($quoteId)
            ->willReturn(true);

        $this->eventManagerMock->expects($this->once())->method('dispatch');
        $this->quoteManagerMock->expects($this->once())->method('expire');
        $this->checkoutSessionMock->expects($this->once())->method('clearQuote');
        $this->customerSessionMock->expects($this->once())->method('setCustomerId')->with(null)->willReturnSelf();

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
        $quoteId = 10;

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
        $this->customerSessionMock
            ->expects($this->atLeastOnce())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        // Mock the QuoteResourceWrapper calls
        $this->quoteResourceWrapperMock->expects($this->any())
            ->method('isPersistent')
            ->with($quoteId)
            ->willReturn(true);

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
