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
 * Contains positive and negative test cases for Signifyd case guarantee creation flow.
 */
class CreationServiceTest extends \PHPUnit\Framework\TestCase
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

        $this->gateway->expects($this->never())
            ->method('submitCaseForGuarantee');

        $result = $this->service->createForOrder($orderId);
        $this->assertFalse($result);
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

        $this->gateway->expects($this->once())
            ->method('submitCaseForGuarantee')
            ->willThrowException(new GatewayException('Something wrong'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Something wrong');

        $result = $this->service->createForOrder($caseEntity->getOrderId());
        $this->assertFalse($result);
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

        $this->gateway->expects($this->once())
            ->method('submitCaseForGuarantee')
            ->with($caseEntity->getCaseId())
            ->willReturn(CaseInterface::GUARANTEE_IN_REVIEW);

        $this->logger->expects($this->never())
            ->method('error');

        $result = $this->service->createForOrder($caseEntity->getOrderId());
        $this->assertTrue($result);

        $updatedCase = $this->getCaseEntity();
        $this->assertEquals(CaseInterface::GUARANTEE_IN_REVIEW, $updatedCase->getGuaranteeDisposition());
        $this->assertEquals(CaseInterface::STATUS_PROCESSING, $updatedCase->getStatus());

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($updatedCase->getOrderId());
        $histories = $order->getStatusHistories();
        $this->assertNotEmpty($histories);

        /** @var OrderStatusHistoryInterface $caseCreationComment */
        $caseCreationComment = array_pop($histories);
        $this->assertInstanceOf(OrderStatusHistoryInterface::class, $caseCreationComment);
        $this->assertEquals('Case Update: Case is submitted for guarantee.', $caseCreationComment->getComment());
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
