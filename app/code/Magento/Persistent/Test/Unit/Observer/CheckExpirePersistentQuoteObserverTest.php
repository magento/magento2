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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckExpirePersistentQuoteObserverTest extends TestCase
{

    use MockCreationTrait;

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
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->persistentHelperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['getControllerAction']
        );
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->requestMock = $this->createPartialMockWithReflection(
            RequestInterface::class,
            [
                'getModuleName', 'setModuleName', 'getActionName', 'setActionName',
                'getParam', 'setParams', 'getParams', 'getCookie', 'isSecure',
                'getRequestUri', 'getServer'  // Custom methods
            ]
        );
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);

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
        $this->quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['getIsPersistent', 'getCustomerIsGuest']
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
     */
    #[DataProvider('requestDataProvider')]
    public function testExecuteWhenPersistentIsEnabled(
        string $refererUri,
        string $requestUri,
        string $expireCounter,
        string $dispatchCounter,
        string $setCustomerIdCounter
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
        $this->eventManagerMock->expects($this->{$dispatchCounter}())->method('dispatch');
        $this->quoteManagerMock->expects($this->{$expireCounter}())->method('expire');
        $this->customerSessionMock
            ->expects($this->{$setCustomerIdCounter}())
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
                'expireCounter'        => 'never',
                'dispatchCounter'      => 'never',
                'setCustomerIdCounter' => 'never',
            ],
            [
                'refererUri'           => 'checkout',
                'requestUri'           => 'checkout',
                'expireCounter'        => 'never',
                'dispatchCounter'      => 'never',
                'setCustomerIdCounter' => 'never',
            ],
            [
                'refererUri'           => 'index',
                'requestUri'           => 'checkout',
                'expireCounter'        => 'never',
                'dispatchCounter'      => 'never',
                'setCustomerIdCounter' => 'never',
            ],
            [
                'refererUri'           => 'index',
                'requestUri'           => 'index',
                'expireCounter'        => 'once',
                'dispatchCounter'      => 'once',
                'setCustomerIdCounter' => 'once',
            ],
        ];
    }
}
