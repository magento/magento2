<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Contains test cases for canceling Signifyd guarantee flow.
 */
class CancelingServiceTest extends \PHPUnit\Framework\TestCase
{
    private static $caseId = 123;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Gateway|MockObject
     */
    private $gateway;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var CancelingService;
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['cancelGuarantee'])
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->service = $this->objectManager->create(CancelingService::class, [
            'gateway' => $this->gateway,
            'logger' => $this->logger
        ]);
    }

    /**
     * Checks a test case, when Signifyd guarantee was canceled.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CancelingService::cancelForOrder
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testCancelForOrderWithCanceledGuarantee()
    {
        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId(self::$caseId);
        $caseEntity->setGuaranteeDisposition(CaseInterface::GUARANTEE_CANCELED);
        $caseRepository->save($caseEntity);

        $this->gateway->expects(self::never())
            ->method('cancelGuarantee');

        $this->logger->expects(self::never())
            ->method('error');

        $result = $this->service->cancelForOrder($caseEntity->getOrderId());
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when Signifyd gateway throws an exception.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CancelingService::cancelForOrder
     * @magentoDataFixture Magento/Signifyd/_files/approved_case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testCancelForOrderWithFailedRequest()
    {
        $exceptionMessage = 'Something wrong.';
        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId(self::$caseId);

        $this->gateway->expects(self::once())
            ->method('cancelGuarantee')
            ->with(self::equalTo(self::$caseId))
            ->willThrowException(new GatewayException($exceptionMessage));

        $this->logger->expects(self::once())
            ->method('error')
            ->with(self::equalTo($exceptionMessage));

        $result = $this->service->cancelForOrder($caseEntity->getOrderId());
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when request to cancel is submitted and case entity is updated successfully.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CancelingService::cancelForOrder
     * @magentoDataFixture Magento/Signifyd/_files/approved_case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testCancelForOrder()
    {
        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId(self::$caseId);

        $this->gateway->expects(self::once())
            ->method('cancelGuarantee')
            ->with(self::equalTo(self::$caseId))
            ->willReturn(CaseInterface::GUARANTEE_CANCELED);

        $this->logger->expects(self::never())
            ->method('error');

        $result = $this->service->cancelForOrder($caseEntity->getOrderId());
        self::assertTrue($result);

        $updatedCase = $caseRepository->getByCaseId(self::$caseId);
        self::assertEquals(CaseInterface::GUARANTEE_CANCELED, $updatedCase->getGuaranteeDisposition());

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($updatedCase->getOrderId());
        $histories = $order->getStatusHistories();
        self::assertNotEmpty($histories);

        /** @var OrderStatusHistoryInterface $caseCreationComment */
        $caseCreationComment = array_pop($histories);
        self::assertInstanceOf(OrderStatusHistoryInterface::class, $caseCreationComment);
        self::assertEquals('Case Update: Case guarantee has been cancelled.', $caseCreationComment->getComment());
    }
}
