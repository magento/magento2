<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui;

use Magento\Framework\Api\Filter;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\Ui\VaultCardsConfigProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProviderTest
 *
 * @see \Magento\Vault\Model\Ui\ConfigProvider
 */
class VaultCardsConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentTokenRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenRepositoryMock;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var VaultCardsConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var VaultPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultPayment;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->paymentTokenRepositoryMock = $this->getMockBuilder(PaymentTokenRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultPayment = $this->getMock(VaultPaymentInterface::class);
        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->store = $this->getMock(StoreInterface::class);

        $this->configProvider = new VaultCardsConfigProvider(
            $this->customerSessionMock,
            $this->paymentTokenRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->storeManager,
            $this->vaultPayment
        );
    }

    /**
     * Run test for getConfig method
     */
    public function testGetConfig()
    {
        $customerId = 1;
        $storeId = 1;
        $vaultPaymentCode = "vault_decorator_code";

        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultMock = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->with(null)
            ->willReturn($this->store);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn(true);

        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->filterBuilderMock->expects(self::once())
            ->method('setField')
            ->with(PaymentTokenInterface::CUSTOMER_ID)
            ->willReturnSelf();
        $this->filterBuilderMock->expects(self::once())
            ->method('setValue')
            ->with($customerId)
            ->willReturnSelf();
        $this->filterBuilderMock->expects(self::once())
            ->method('create')
            ->willReturn($filterMock);

        $this->searchCriteriaBuilderMock->expects(self::once())
            ->method('addFilters')
            ->with([$filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->paymentTokenRepositoryMock->expects(self::once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects(self::once())
            ->method('getItems')
            ->willReturn([$tokenMock]);

        $this->vaultPayment->expects(static::exactly(1))
            ->method('getCode')
            ->willReturn($vaultPaymentCode);

        $tokenMock->expects(self::once())
            ->method('getPublicHash')
            ->willReturn('test-hash');

        $config = $this->configProvider->getConfig();

        self::assertNotEmpty($config);
        self::assertArrayHasKey('payment', $config);
        self::assertArrayHasKey(VaultPaymentInterface::CODE, $config['payment']);

        foreach ($config['payment'] as $item) {
            foreach ($item as $paymentToken) {
                self::assertArrayHasKey('token', $paymentToken);
                self::assertArrayHasKey('title', $paymentToken);
            }
        }
    }

    /**
     * Run test for getConfig method
     */
    public function testGetConfigEmpty()
    {
        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->filterBuilderMock->expects(self::never())
            ->method('setField');
        $this->filterBuilderMock->expects(self::never())
            ->method('setValue');
        $this->filterBuilderMock->expects(self::never())
            ->method('create');

        $this->searchCriteriaBuilderMock->expects(self::never())
            ->method('addFilters');
        $this->searchCriteriaBuilderMock->expects(self::never())
            ->method('create');

        $this->paymentTokenRepositoryMock->expects(self::never())
            ->method('getList');

        $config = $this->configProvider->getConfig();

        self::assertEmpty($config);
    }
}
