<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\ResourceModel\PaymentToken;
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
    private $paymentTokenRepository;

    /**
     * @var PaymentTokenResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenResourceModel;

    /**
     * @var PaymentTokenResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceModel;

    /**
     * @var PaymentTokenFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encryptor;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactory;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->paymentTokenRepository = $this->getMockBuilder(PaymentTokenRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->paymentTokenResourceModel = $this->getMockBuilder(PaymentTokenResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceModel = $this->getMockBuilder(PaymentTokenResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactory = $this->getMockBuilder(PaymentTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactory = $this->getMockBuilder(PaymentTokenSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptor = $this->getMock(EncryptorInterface::class);
        $this->dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenManagement = new PaymentTokenManagement(
            $this->paymentTokenRepository,
            $this->paymentTokenResourceModel,
            $this->paymentTokenFactory,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->searchResultsFactory,
            $this->encryptor,
            $this->dateTimeFactory
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

        $this->filterBuilder->expects(self::once())
            ->method('setField')
            ->with(PaymentTokenInterface::CUSTOMER_ID)
            ->willReturnSelf();
        $this->filterBuilder->expects(self::once())
            ->method('setValue')
            ->with(1)
            ->willReturnSelf();
        $this->filterBuilder->expects(self::once())
            ->method('create')
            ->willReturn($filterMock);

        $this->searchCriteriaBuilder->expects(self::once())
            ->method('addFilters')
            ->with([$filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->paymentTokenRepository->expects(self::once())
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

        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByOrderPaymentId')
            ->with(1)
            ->willReturn(['token-data']);

        $this->paymentTokenFactory->expects(self::once())
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
        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByOrderPaymentId')
            ->with(1)
            ->willReturn([]);

        $this->paymentTokenFactory->expects(self::never())
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

        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByGatewayToken')
            ->with('token', 1, 1)
            ->willReturn(['token-data']);

        $this->paymentTokenFactory->expects(self::once())
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
        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByGatewayToken')
            ->with('some-not-exists-token', 1, 1)
            ->willReturn([]);

        $this->paymentTokenFactory->expects(self::never())
            ->method('create');

        self::assertEquals(null, $this->paymentTokenManagement->getByGatewayToken('some-not-exists-token', 1, 1));
    }

    /**
     * Run test for getByGatewayToken method (return NULL)
     */
    public function testGetByPublicHash()
    {
        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByPublicHash')
            ->with('some-not-exists-token', 1)
            ->willReturn([]);

        $this->paymentTokenFactory->expects(self::never())
            ->method('create');

        self::assertEquals(null, $this->paymentTokenManagement->getByPublicHash('some-not-exists-token', 1));
    }

    /**
     * Run test for saveTokenWithPaymentLink method
     */
    public function testSaveTokenWithPaymentLinkNoDuplicate()
    {
        /** @var OrderPaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->getMock(OrderPaymentInterface::class);
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMock(PaymentTokenInterface::class);

        $customerId = 1;
        $entityId = 1;
        $publicHash = 'some-not-existing-token';
        $paymentId = 1;

        $tokenMock->expects(static::atLeastOnce())
            ->method('getPublicHash')
            ->willReturn($publicHash);
        $tokenMock->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByPublicHash')
            ->with($publicHash, 1)
            ->willReturn([]);

        $this->paymentTokenFactory->expects(self::never())
            ->method('create');

        $tokenMock->expects(self::once())
            ->method('getEntityId')
            ->willReturn($entityId);
        $this->paymentTokenRepository->expects(self::once())
            ->method('save')
            ->with($tokenMock);

        $paymentMock->expects(self::once())
            ->method('getEntityId')
            ->willReturn($paymentId);

        $this->paymentTokenResourceModel->expects(static::once())
            ->method('addLinkToOrderPayment')
            ->with($entityId, $paymentId);

        $this->paymentTokenManagement->saveTokenWithPaymentLink($tokenMock, $paymentMock);
    }

    /**
     * Run test for saveTokenWithPaymentLink method
     */
    public function testSaveTokenWithPaymentLinkWithDuplicateTokenVisible()
    {
        /** @var OrderPaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->getMock(OrderPaymentInterface::class);
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMock(PaymentTokenInterface::class);
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $duplicateToken */
        $duplicateToken = $this->getMock(PaymentTokenInterface::class);

        $entityId = 1;
        $customerId = 1;
        $paymentId = 1;
        $publicHash = 'existing-token';
        $duplicateTokenData = [
            'entity_id' => $entityId
        ];

        $tokenMock->expects(static::atLeastOnce())
            ->method('getPublicHash')
            ->willReturn($publicHash);
        $tokenMock->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($duplicateTokenData);

        $this->paymentTokenFactory->expects(self::once())
            ->method('create')
            ->with(['data' => $duplicateTokenData])
            ->willReturn($duplicateToken);
        $tokenMock->expects(static::once())
            ->method('getIsVisible')
            ->willReturn(true);
        $duplicateToken->expects(static::once())
            ->method('getEntityId')
            ->willReturn($entityId);
        $tokenMock->expects(self::once())
            ->method('getEntityId')
            ->willReturn($entityId);
        $this->paymentTokenRepository->expects(self::once())
            ->method('save')
            ->with($tokenMock);

        $paymentMock->expects(self::once())
            ->method('getEntityId')
            ->willReturn($paymentId);

        $this->paymentTokenResourceModel->expects(static::once())
            ->method('addLinkToOrderPayment')
            ->with($entityId, $paymentId);

        $this->paymentTokenManagement->saveTokenWithPaymentLink($tokenMock, $paymentMock);
    }

    /**
     * Run test for saveTokenWithPaymentLink method
     */
    public function testSaveTokenWithPaymentLinkWithDuplicateTokenNotVisible()
    {
        /** @var OrderPaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->getMock(OrderPaymentInterface::class);
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMock(PaymentTokenInterface::class);
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $duplicateToken */
        $duplicateToken = $this->getMock(PaymentTokenInterface::class);

        $entityId = 1;
        $newEntityId = 1;
        $paymentId = 1;
        $customerId = 1;
        $gatewayToken = 'xs4vf3';
        $publicHash = 'existing-token';
        $duplicateTokenData = [
            'entity_id' => $entityId
        ];
        $newHash = 'new-token2';

        $tokenMock->expects(static::atLeastOnce())
            ->method('getPublicHash')
            ->willReturn($publicHash);
        $tokenMock->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentTokenResourceModel->expects(self::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($duplicateTokenData);

        $this->paymentTokenFactory->expects(self::once())
            ->method('create')
            ->with(['data' => $duplicateTokenData])
            ->willReturn($duplicateToken);
        $tokenMock->expects(static::atLeastOnce())
            ->method('getIsVisible')
            ->willReturn(false);
        $tokenMock->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $tokenMock->expects(static::atLeastOnce())
            ->method('getGatewayToken')
            ->willReturn($gatewayToken);

        $this->encryptor->expects(static::once())
            ->method('getHash')
            ->with($publicHash . $gatewayToken)
            ->willReturn($newHash);
        $tokenMock->expects(static::once())
            ->method('setPublicHash')
            ->with($newHash);

        $this->paymentTokenRepository->expects(self::once())
            ->method('save')
            ->with($tokenMock);
        $tokenMock->expects(static::atLeastOnce())
            ->method('getEntityId')
            ->willReturn($newEntityId);

        $paymentMock->expects(self::atLeastOnce())
            ->method('getEntityId')
            ->willReturn($paymentId);
        $this->paymentTokenResourceModel->expects(static::once())
            ->method('addLinkToOrderPayment')
            ->with($newEntityId, $paymentId);

        $this->paymentTokenManagement->saveTokenWithPaymentLink($tokenMock, $paymentMock);
    }

    public function testGetVisibleAvailableTokens()
    {
        $customerId = 1;

        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResult = $this->getMockForAbstractClass(PaymentTokenSearchResultsInterface::class);
        $token = $this->getMockForAbstractClass(PaymentTokenInterface::class);

        $customerFilter = $this->createExpectedFilter(PaymentTokenInterface::CUSTOMER_ID, $customerId, 0);
        $visibilityFilter = $this->createExpectedFilter(PaymentTokenInterface::IS_VISIBLE, true, 1);
        $isActiveFilter = $this->createExpectedFilter(PaymentTokenInterface::IS_ACTIVE, true, 2);

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

        $this->searchCriteriaBuilder->expects(self::exactly(4))
            ->method('addFilters')
            ->withConsecutive($customerFilter, $visibilityFilter, $isActiveFilter, $expiresAtFilter)
            ->willReturnSelf();

        $this->searchCriteriaBuilder->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->paymentTokenRepository->expects(self::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);

        $searchResult->expects(self::once())
            ->method('getItems')
            ->willReturn([$token]);

        static::assertEquals(
            [$token],
            $this->paymentTokenManagement->getVisibleAvailableTokens($customerId)
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
}
