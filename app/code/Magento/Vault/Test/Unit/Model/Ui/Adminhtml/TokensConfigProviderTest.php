<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui\Adminhtml;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class TokensConfigProviderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokensConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Global values
     */
    const STORE_ID = 1;
    const ORDER_ID = 2;
    const ORDER_PAYMENT_ENTITY_ID = 3;
    const ENTITY_ID = 4;
    const VAULT_PAYMENT_CODE = 'vault_payment';
    const VAULT_PROVIDER_CODE = 'payment';
    /**#@-*/

    /**
     * @var PaymentTokenRepositoryInterface|MockObject
     */
    private $paymentTokenRepository;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var Quote|MockObject
     */
    private $session;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var DateTimeFactory|MockObject
     */
    private $dateTimeFactory;

    /**
     * @var Data|MockObject
     */
    private $paymentDataHelper;

    /**
     * @var VaultPaymentInterface|MockObject
     */
    private $vaultPayment;

    /**
     * @var PaymentTokenManagementInterface|MockObject
     */
    private $paymentTokenManagement;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepository;

    /**
     * @var TokenUiComponentProviderInterface|MockObject
     */
    private $tokenComponentProvider;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TokensConfigProvider
     */
    private $configProvider;

    protected function setUp()
    {
        $this->paymentTokenRepository = $this->getMockBuilder(PaymentTokenRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getReordered'])
            ->getMock();
        $this->dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();
        $this->paymentTokenManagement = $this->getMockBuilder(PaymentTokenManagementInterface::class)
            ->getMockForAbstractClass();
        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        
        $this->objectManager = new ObjectManager($this);

        $this->initStoreMock();

        $this->tokenComponentProvider = $this->createMock(TokenUiComponentProviderInterface::class);

        $this->configProvider = new TokensConfigProvider(
            $this->session,
            $this->paymentTokenRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->storeManager,
            $this->dateTimeFactory,
            [
                self::VAULT_PROVIDER_CODE => $this->tokenComponentProvider
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $this->configProvider,
            'paymentDataHelper',
            $this->paymentDataHelper
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->configProvider,
            'paymentTokenManagement',
            $this->paymentTokenManagement
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->configProvider,
            'orderRepository',
            $this->orderRepository
        );
    }

    /**
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     */
    public function testGetTokensComponentsRegisteredCustomer()
    {
        $customerId = 1;

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with(self::VAULT_PAYMENT_CODE)
            ->willReturn($this->vaultPayment);
        
        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with(self::STORE_ID)
            ->willReturn(true);
        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn(self::VAULT_PROVIDER_CODE);

        /** @var PaymentTokenInterface|MockObject $token */
        $token = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $tokenUiComponent = $this->getTokenUiComponentProvider($token);

        $searchCriteria = $this->getSearchCriteria($customerId, self::ENTITY_ID, self::VAULT_PROVIDER_CODE);

        $date = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeFactory->expects(static::once())
            ->method('create')
            ->with("now", new \DateTimeZone('UTC'))
            ->willReturn($date);
        $date->expects(static::once())
            ->method('format')
            ->with('Y-m-d 00:00:00')
            ->willReturn('2015-01-01 00:00:00');

        $searchResult = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $this->paymentTokenRepository->expects(self::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);

        $searchResult->expects(self::once())
            ->method('getItems')
            ->willReturn([$token]);

        static::assertEquals([$tokenUiComponent], $this->configProvider->getTokensComponents(self::VAULT_PAYMENT_CODE));
    }

    /**
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     */
    public function testGetTokensComponentsGuestCustomer()
    {
        $customerId = null;

        $this->initStoreMock();

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with(self::VAULT_PAYMENT_CODE)
            ->willReturn($this->vaultPayment);

        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with(self::STORE_ID)
            ->willReturn(true);
        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn(self::VAULT_PROVIDER_CODE);

        /** @var PaymentTokenInterface|MockObject $token */
        $token = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->session->expects(static::once())
            ->method('getReordered')
            ->willReturn(self::ORDER_ID);
        $this->orderRepository->expects(static::once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($this->getOrderMock());
        $this->paymentTokenManagement->expects(static::once())
            ->method('getByPaymentId')
            ->with(self::ORDER_PAYMENT_ENTITY_ID)
            ->willReturn($token);
        $token->expects(static::once())
            ->method('getEntityId')
            ->willReturn(self::ENTITY_ID);

        $tokenUiComponent = $this->getTokenUiComponentProvider($token);

        $searchCriteria = $this->getSearchCriteria($customerId, self::ENTITY_ID, self::VAULT_PROVIDER_CODE);

        $date = $this->getMockBuilder('DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeFactory->expects(static::once())
            ->method('create')
            ->with("now", new \DateTimeZone('UTC'))
            ->willReturn($date);
        $date->expects(static::once())
            ->method('format')
            ->with('Y-m-d 00:00:00')
            ->willReturn('2015-01-01 00:00:00');

        $searchResult = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $this->paymentTokenRepository->expects(self::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);

        $searchResult->expects(self::once())
            ->method('getItems')
            ->willReturn([$token]);

        static::assertEquals([$tokenUiComponent], $this->configProvider->getTokensComponents(self::VAULT_PAYMENT_CODE));
    }

    /**
     * @param \Exception $exception
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     * @dataProvider getTokensComponentsGuestCustomerExceptionsProvider
     */
    public function testGetTokensComponentsGuestCustomerOrderNotFound($exception)
    {
        $customerId = null;

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with(self::VAULT_PAYMENT_CODE)
            ->willReturn($this->vaultPayment);

        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with(self::STORE_ID)
            ->willReturn(true);
        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn(self::VAULT_PROVIDER_CODE);

        $this->session->expects(static::once())
            ->method('getReordered')
            ->willReturn(self::ORDER_ID);
        $this->orderRepository->expects(static::once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willThrowException($exception);

        $this->filterBuilder->expects(static::once())
            ->method('setField')
            ->with(PaymentTokenInterface::ENTITY_ID)
            ->willReturnSelf();
        $this->filterBuilder->expects(static::never())
            ->method('setValue');
        $this->searchCriteriaBuilder->expects(self::never())
            ->method('addFilters');

        static::assertEmpty($this->configProvider->getTokensComponents(self::VAULT_PAYMENT_CODE));
    }

    /**
     * Set of catching exception types
     * @return array
     */
    public function getTokensComponentsGuestCustomerExceptionsProvider()
    {
        return [
            [new InputException()],
            [new NoSuchEntityException()],
        ];
    }

    /**
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     */
    public function testGetTokensComponentsEmptyComponentProvider()
    {
        $customerId = 1;

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->initStoreMock();

        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with(self::VAULT_PAYMENT_CODE)
            ->willReturn($this->vaultPayment);

        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with(self::STORE_ID)
            ->willReturn(false);

        $this->paymentTokenRepository->expects(static::never())
            ->method('getList');

        $configProvider = new TokensConfigProvider(
            $this->session,
            $this->paymentTokenRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->storeManager,
            $this->dateTimeFactory
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $configProvider,
            'paymentDataHelper',
            $this->paymentDataHelper
        );

        static::assertEmpty($configProvider->getTokensComponents(self::VAULT_PAYMENT_CODE));
    }

    /**
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     */
    public function testGetTokensComponentsForGuestCustomerWithoutStoredTokens()
    {
        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with(self::VAULT_PAYMENT_CODE)
            ->willReturn($this->vaultPayment);

        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with(self::STORE_ID)
            ->willReturn(true);
        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn(self::VAULT_PROVIDER_CODE);

        $this->session->expects(static::once())
            ->method('getReordered')
            ->willReturn(self::ORDER_ID);
        $this->orderRepository->expects(static::once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($this->getOrderMock());

        $this->paymentTokenManagement->expects(static::once())
            ->method('getByPaymentId')
            ->with(self::ORDER_PAYMENT_ENTITY_ID)
            ->willReturn(null);

        $this->filterBuilder->expects(static::once())
            ->method('setField')
            ->with(PaymentTokenInterface::ENTITY_ID)
            ->willReturnSelf();
        $this->filterBuilder->expects(static::never())
            ->method('setValue');

        $this->searchCriteriaBuilder->expects(static::never())
            ->method('addFilters');

        static::assertEmpty($this->configProvider->getTokensComponents(self::VAULT_PAYMENT_CODE));
    }

    /**
     * Create mock object for store
     */
    private function initStoreMock()
    {
        $this->store = $this->createMock(StoreInterface::class);
        $this->store->expects(static::any())
            ->method('getId')
            ->willReturn(self::STORE_ID);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->expects(static::any())
            ->method('getStore')
            ->with(null)
            ->willReturn($this->store);
    }

    /**
     * Returns order mock with order payment mock
     * @return OrderInterface
     */
    private function getOrderMock()
    {
        /** @var OrderInterface|MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();
        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->getMockForAbstractClass();

        $order->expects(static::once())
            ->method('getPayment')
            ->willReturn($orderPayment);
        $orderPayment->expects(static::once())
            ->method('getEntityId')
            ->willReturn(self::ORDER_PAYMENT_ENTITY_ID);

        return $order;
    }

    /**
     * Get mock for token ui component provider
     * @param PaymentTokenInterface $token
     * @return TokenUiComponentInterface|MockObject
     */
    private function getTokenUiComponentProvider($token)
    {
        $tokenUiComponent = $this->createMock(TokenUiComponentInterface::class);
        $this->tokenComponentProvider->expects(static::once())
            ->method('getComponentForToken')
            ->with($token)
            ->willReturn($tokenUiComponent);

        return $tokenUiComponent;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param int $atIndex
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createExpectedFilter($field, $value, $atIndex)
    {
        $filterObject = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder->expects(new MethodInvokedAtIndex($atIndex))
            ->method('setField')
            ->with($field)
            ->willReturnSelf();
        $this->filterBuilder->expects(new MethodInvokedAtIndex($atIndex))
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();
        $this->filterBuilder->expects(new MethodInvokedAtIndex($atIndex))
            ->method('create')
            ->willReturn($filterObject);

        return $filterObject;
    }

    /**
     * Build search criteria
     * @param int $customerId
     * @param int $entityId
     * @param string $vaultProviderCode
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSearchCriteria($customerId, $entityId, $vaultProviderCode)
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerFilter = $customerId ? $this->createExpectedFilter(PaymentTokenInterface::CUSTOMER_ID, $customerId, 0)
            : $this->createExpectedFilter(PaymentTokenInterface::ENTITY_ID, $entityId, 0);
        $codeFilter = $this->createExpectedFilter(
            PaymentTokenInterface::PAYMENT_METHOD_CODE,
            $vaultProviderCode,
            1
        );

        $isActiveFilter = $this->createExpectedFilter(PaymentTokenInterface::IS_ACTIVE, 1, 2);

        // express at expectations
        $expiresAtFilter = $this->createExpectedFilter(
            PaymentTokenInterface::EXPIRES_AT,
            '2015-01-01 00:00:00',
            3
        );
        $this->filterBuilder->expects(static::once())
            ->method('setConditionType')
            ->with('gt')
            ->willReturnSelf();

        $this->searchCriteriaBuilder->expects(self::exactly(4))
            ->method('addFilters')
            ->willReturnMap(
                [
                    [$customerFilter, $this->searchCriteriaBuilder],
                    [$codeFilter, $this->searchCriteriaBuilder],
                    [$expiresAtFilter, $this->searchCriteriaBuilder],
                    [$isActiveFilter, $this->searchCriteriaBuilder],
                ]
            );

        $this->searchCriteriaBuilder->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteria);

        return $searchCriteria;
    }
}
