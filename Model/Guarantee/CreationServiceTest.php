<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Contains positive and negative test cases for Signifyd case guarantee creation flow.
 */
class CreationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreationService
     */
    private $service;

    /**
     * @var Gateway|MockObject
     */
    private $gateway;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var ObjectManager $objectManager */
        $this->objectManager = Bootstrap::getObjectManager();

        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['submitCaseForGuarantee'])
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = $this->objectManager->create(CreationService::class, [
            'gateway' => $this->gateway,
            'logger' => $this->logger
        ]);
    }

    /**
     * Checks a test case, when Signifyd case entity cannot be found
     * for a specified order.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CreationService::createForOrder
     */
    public function testCreateWithoutCaseEntity()
    {
        $orderId = 123;

        $this->gateway->expects(self::never())
            ->method('submitCaseForGuarantee');

        $result = $this->service->createForOrder($orderId);
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when request is failing.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testCreateWithFailedRequest()
    {
        $caseEntity = $this->getCaseEntity();

        $this->gateway->expects(self::once())
            ->method('submitCaseForGuarantee')
            ->willThrowException(new GatewayException('Something wrong'));

        $this->logger->expects(self::once())
            ->method('error')
            ->with('Something wrong');

        $result = $this->service->createForOrder($caseEntity->getOrderId());
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when case entity is updated successfully.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testCreate()
    {
        $caseEntity = $this->getCaseEntity();

        $this->gateway->expects(self::once())
            ->method('submitCaseForGuarantee')
            ->with($caseEntity->getCaseId())
            ->willReturn(CaseInterface::GUARANTEE_IN_REVIEW);

        $this->logger->expects(self::never())
            ->method('error');

        $result = $this->service->createForOrder($caseEntity->getOrderId());
        self::assertTrue($result);

        $updatedCase = $this->getCaseEntity();
        self::assertEquals(CaseInterface::GUARANTEE_IN_REVIEW, $updatedCase->getGuaranteeDisposition());
        self::assertEquals(CaseInterface::STATUS_PROCESSING, $updatedCase->getStatus());

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($updatedCase->getOrderId());
        $histories = $order->getStatusHistories();
        static::assertNotEmpty($histories);

        /** @var OrderStatusHistoryInterface $caseCreationComment */
        $caseCreationComment = array_pop($histories);
        static::assertInstanceOf(OrderStatusHistoryInterface::class, $caseCreationComment);
        static::assertEquals('Case Update: Case is submitted for guarantee.', $caseCreationComment->getComment());
    }

    /**
     * Gets case entity.
     *
     * @return \Magento\Signifyd\Api\Data\CaseInterface|null
     */
    private function getCaseEntity()
    {
        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        return $caseRepository->getByCaseId(123);
    }
}
