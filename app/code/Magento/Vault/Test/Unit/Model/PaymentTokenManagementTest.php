<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\Api\Filter;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Magento\Vault\Model\ResourceModel\PaymentToken as PaymentTokenResourceModel;

/**
 * Class PaymentTokenManagementTest
 *
 * @see \Magento\Vault\Model\PaymentTokenManagement
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentTokenManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var PaymentTokenRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenRepositoryMock;

    /**
     * @var PaymentTokenResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenResourceModelMock;

    /**
     * @var PaymentTokenResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModelMock;

    /**
     * @var PaymentTokenFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenFactoryMock;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsFactoryMock;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->paymentTokenRepositoryMock = $this->getMockBuilder(PaymentTokenRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->paymentTokenResourceModelMock = $this->getMockBuilder(PaymentTokenResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceModelMock = $this->getMockBuilder(PaymentTokenResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactoryMock = $this->getMockBuilder(PaymentTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactoryMock = $this->getMockBuilder(PaymentTokenSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenManagement = new PaymentTokenManagement(
            $this->paymentTokenRepositoryMock,
            $this->paymentTokenResourceModelMock,
            $this->paymentTokenFactoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->searchResultsFactoryMock
        );
    }

    /**
     * Run test for getListByCustomerId method
     */
    public function testGetListByCustomerId()
    {
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();
        /** @var Filter| $filterMock */
        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResult = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();

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
            ->willReturn($searchCriteria);

        $this->paymentTokenRepositoryMock->expects(self::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);

        $searchResult->expects(self::once())
            ->method('getItems')
            ->willReturn([$tokenMock]);

        self::assertEquals([$tokenMock], $this->paymentTokenManagement->getListByCustomerId(1));
    }

    /**
     * Run test for getByPaymentId method
     */
    public function testGetByPaymentId()
    {
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->paymentTokenResourceModelMock->expects(self::once())
            ->method('getByOrderPaymentId')
            ->with(1)
            ->willReturn(['token-data']);

        $this->paymentTokenFactoryMock->expects(self::once())
            ->method('create')
            ->with(['data' => ['token-data']])
            ->willReturn($tokenMock);

        self::assertEquals($tokenMock, $this->paymentTokenManagement->getByPaymentId(1));
    }

    /**
     * Run test for getByPaymentId method (return NULL)
     */
    public function testGetByPaymentIdNull()
    {
        $this->paymentTokenResourceModelMock->expects(self::once())
            ->method('getByOrderPaymentId')
            ->with(1)
            ->willReturn([]);

        $this->paymentTokenFactoryMock->expects(self::never())
            ->method('create');

        self::assertEquals(null, $this->paymentTokenManagement->getByPaymentId(1));
    }

    /**
     * Run test for getByGatewayToken method
     */
    public function testGetByGatewayToken()
    {
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->paymentTokenResourceModelMock->expects(self::once())
            ->method('getByGatewayToken')
            ->with('token', 1, 1)
            ->willReturn(['token-data']);

        $this->paymentTokenFactoryMock->expects(self::once())
            ->method('create')
            ->with(['data' => ['token-data']])
            ->willReturn($tokenMock);

        self::assertEquals($tokenMock, $this->paymentTokenManagement->getByGatewayToken('token', 1, 1));
    }

    /**
     * Run test for getByGatewayToken method (return NULL)
     */
    public function testGetByGatewayTokenNull()
    {
        $this->paymentTokenResourceModelMock->expects(self::once())
            ->method('getByGatewayToken')
            ->with('some-not-exists-token', 1, 1)
            ->willReturn([]);

        $this->paymentTokenFactoryMock->expects(self::never())
            ->method('create');

        self::assertEquals(null, $this->paymentTokenManagement->getByGatewayToken('some-not-exists-token', 1, 1));
    }

    /**
     * Run test for saveTokenWithPaymentLink method
     */
    public function testSaveTokenWithPaymentLink()
    {
        /** @var OrderPaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->getMock(OrderPaymentInterface::class);
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $tokenMock->expects(self::once())
            ->method('getEntityId')
            ->willReturn(1);
        $this->paymentTokenRepositoryMock->expects(self::once())
            ->method('save')
            ->with($tokenMock);

        $paymentMock->expects(self::once())
            ->method('getEntityId');

        $this->paymentTokenManagement->saveTokenWithPaymentLink($tokenMock, $paymentMock);
    }
}
