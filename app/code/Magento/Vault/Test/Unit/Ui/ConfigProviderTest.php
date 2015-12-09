<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Ui;

use Magento\Framework\Api\Filter;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Vault\Model\Ui\ConfigProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;

/**
 * Class ConfigProviderTest
 *
 * @see \Magento\Vault\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
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
    }

    /**
     * Run test for getConfig method
     */
    public function testGetConfig()
    {
        /** @var Filter|\PHPUnit_Framework_MockObject_MockObject $filterMock */
        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PaymentTokenSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject $searchResultMock */
        $searchResultMock = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();

        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->filterBuilderMock->expects(self::once())
            ->method('setField')
            ->with(PaymentTokenInterface::CUSTOMER_ID)
            ->willReturnSelf();
        $this->filterBuilderMock->expects(self::once())
            ->method('setValue')
            ->with(1)
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

        $tokenMock->expects(self::once())
            ->method('getPublicHash')
            ->willReturn('test-hash');

        $configProvider = new ConfigProvider(
            $this->customerSessionMock,
            $this->paymentTokenRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock
        );

        $config = $configProvider->getConfig();

        self::assertNotEmpty($config);
        self::assertArrayHasKey('payment', $config);
        self::assertArrayHasKey(ConfigProvider::CODE, $config['payment']);

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

        $configProvider = new ConfigProvider(
            $this->customerSessionMock,
            $this->paymentTokenRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock
        );

        $config = $configProvider->getConfig();

        self::assertEmpty($config);
    }
}
