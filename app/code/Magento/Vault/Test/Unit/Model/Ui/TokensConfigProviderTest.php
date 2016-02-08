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
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\Ui\TokensConfigProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProviderTest
 *
 * @see \Magento\Vault\Model\Ui\TokensConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class TokensConfigProviderTest extends \PHPUnit_Framework_TestCase
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
    }

    public function testGetConfig()
    {
        $customerId = 1;
        $visible = true;
        $storeId = 1;

        $vaultProviderCode = "vault_provider_code";

        $expectedConfig = [
            'payment' => [
                VaultPaymentInterface::CODE => [
                    VaultPaymentInterface::CODE . '_item_' . '0' => [
                        'config' => ['token_code' => 'code'],
                        'component' => 'Vendor_Module/js/vault_component'
                    ]
                ]
            ]
        ];

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultMock = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();
        $tokenUiComponentProvider = $this->getMock(TokenUiComponentProviderInterface::class);
        $tokenUiComponent = $this->getMock(TokenUiComponentInterface::class);

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

        $customerFilterMock = $this->createExpectedFilter(PaymentTokenInterface::CUSTOMER_ID, $customerId, 0);
        $visibilityFilterMock = $this->createExpectedFilter(PaymentTokenInterface::IS_VISIBLE, $visible, 1);

        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn($vaultProviderCode);

        $codeFilterMock = $this->createExpectedFilter(
            PaymentTokenInterface::PAYMENT_METHOD_CODE,
            $vaultProviderCode,
            2
        );

        $this->searchCriteriaBuilderMock->expects(self::once())
            ->method('addFilters')
            ->with([$customerFilterMock, $visibilityFilterMock, $codeFilterMock])
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

        $tokenUiComponentProvider->expects(static::once())
            ->method('getComponentForToken')
            ->with($tokenMock)
            ->willReturn($tokenUiComponent);
        $tokenUiComponent->expects(static::once())
            ->method('getConfig')
            ->willReturn(['token_code' => 'code']);
        $tokenUiComponent->expects(static::once())
            ->method('getName')
            ->willReturn('Vendor_Module/js/vault_component');

        $configProvider = new TokensConfigProvider(
            $this->customerSessionMock,
            $this->paymentTokenRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->storeManager,
            $this->vaultPayment,
            [
                $vaultProviderCode => $tokenUiComponentProvider
            ]
        );

        static::assertEquals(
            $expectedConfig,
            $configProvider->getConfig()
        );
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
        $this->filterBuilderMock->expects(new MethodInvokedAtIndex($atIndex))
            ->method('setField')
            ->with($field)
            ->willReturnSelf();
        $this->filterBuilderMock->expects(new MethodInvokedAtIndex($atIndex))
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();
        $this->filterBuilderMock->expects(new MethodInvokedAtIndex($atIndex))
            ->method('create')
            ->willReturn($filterObject);

        return $filterObject;
    }

    public function testGetConfigNonRegisteredCustomer()
    {
        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->paymentTokenRepositoryMock->expects(self::never())
            ->method('getList');

        $configProvider = new TokensConfigProvider(
            $this->customerSessionMock,
            $this->paymentTokenRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->storeManager,
            $this->vaultPayment
        );
        $config = $configProvider->getConfig();

        self::assertEmpty($config);
    }

    public function testGetConfigNoActiveVaultProvider()
    {
        $customerId = 1;
        $storeId = 1;
        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

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
            ->willReturn(false);

        $this->paymentTokenRepositoryMock->expects(self::never())
            ->method('getList');

        $configProvider = new TokensConfigProvider(
            $this->customerSessionMock,
            $this->paymentTokenRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->storeManager,
            $this->vaultPayment
        );
        $config = $configProvider->getConfig();

        self::assertEmpty($config);
    }
}
