<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui\Adminhtml;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Payment\Helper\Data;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class TokensConfigProviderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokensConfigProviderTest extends \PHPUnit_Framework_TestCase
{
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
     * @var ObjectManager
     */
    private $objectManager;

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
            ->setMethods(['getCustomerId'])
            ->getMock();
        $this->dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $this->vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     */
    public function testGetTokensComponents()
    {
        $storeId = 1;
        $customerId = 2;
        $vaultPaymentCode = 'vault_payment';
        $vaultProviderCode = 'payment';

        $this->initStoreMock();

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with($vaultPaymentCode)
            ->willReturn($this->vaultPayment);
        
        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn(true);
        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn($vaultProviderCode);

        /** @var PaymentTokenInterface|MockObject $token */
        $token = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        list($tokenUiComponent, $tokenUiComponentProvider) = $this->getTokenUiComponentProvider($token);

        $searchCriteria = $this->getSearchCriteria($customerId, $vaultProviderCode);

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

        $configProvider = new TokensConfigProvider(
            $this->session,
            $this->paymentTokenRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->storeManager,
            $this->dateTimeFactory,
            [
                $vaultProviderCode => $tokenUiComponentProvider
            ]
        );
        
        $this->objectManager->setBackwardCompatibleProperty(
            $configProvider,
            'paymentDataHelper',
            $this->paymentDataHelper
        );

        static::assertEquals([$tokenUiComponent], $configProvider->getTokensComponents($vaultPaymentCode));
    }

    /**
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     */
    public function testGetTokensComponentsNotExistsCustomer()
    {
        $vaultPaymentCode = 'vault_payment';
        $this->store = $this->getMock(StoreInterface::class);
        $this->storeManager = $this->getMock(StoreManagerInterface::class);

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->storeManager->expects(static::never())
            ->method('getStore');

        $configProvider = new TokensConfigProvider(
            $this->session,
            $this->paymentTokenRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->storeManager,
            $this->dateTimeFactory
        );

        static::assertEmpty($configProvider->getTokensComponents($vaultPaymentCode));
    }

    /**
     * @covers \Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider::getTokensComponents
     */
    public function testGetTokensComponentsEmptyComponentProvider()
    {
        $storeId = 1;
        $customerId = 2;
        $vaultPaymentCode = 'vault_payment';

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->initStoreMock();

        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with($vaultPaymentCode)
            ->willReturn($this->vaultPayment);

        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
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

        static::assertEmpty($configProvider->getTokensComponents($vaultPaymentCode));
    }

    /**
     * Create mock object for store
     */
    private function initStoreMock()
    {
        $storeId = 1;

        $this->store = $this->getMock(StoreInterface::class);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->with(null)
            ->willReturn($this->store);
    }

    /**
     * Get mock for token ui component provider
     * @param PaymentTokenInterface $token
     * @return array
     */
    private function getTokenUiComponentProvider($token)
    {
        $tokenUiComponent = $this->getMock(TokenUiComponentInterface::class);

        $tokenUiComponentProvider = $this->getMock(TokenUiComponentProviderInterface::class);
        $tokenUiComponentProvider->expects(static::once())
            ->method('getComponentForToken')
            ->with($token)
            ->willReturn($tokenUiComponent);

        return [$tokenUiComponent, $tokenUiComponentProvider];
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
     * @param string $vaultProviderCode
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSearchCriteria($customerId, $vaultProviderCode)
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerFilter = $this->createExpectedFilter(PaymentTokenInterface::CUSTOMER_ID, $customerId, 0);
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

        $this->searchCriteriaBuilder->expects(self::once())
            ->method('addFilters')
            ->with([$customerFilter, $codeFilter, $expiresAtFilter, $isActiveFilter])
            ->willReturnSelf();

        $this->searchCriteriaBuilder->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteria);

        return $searchCriteria;
    }
}
