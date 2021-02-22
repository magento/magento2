<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\Guarantee;

use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CaseServices\StubUpdatingService;
use Magento\Signifyd\Model\CaseServices\UpdatingServiceFactory;
use Magento\Signifyd\Model\Guarantee\CancelGuaranteeAbility;
use Magento\Signifyd\Model\Guarantee\CancelingService;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Contains test cases for Signifyd guarantee canceling service.
 */
class CancelingServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    private static $orderId = 23;

    /**
     * @var int
     */
    private static $caseId = 123;

    /**
     * @var CancelingService
     */
    private $service;

    /**
     * @var CaseManagementInterface|MockObject
     */
    private $caseManagement;

    /**
     * @var UpdatingServiceFactory|MockObject
     */
    private $updatingFactory;

    /**
     * @var Gateway|MockObject
     */
    private $gateway;

    /**
     * @var CancelGuaranteeAbility|MockObject
     */
    private $guaranteeAbility;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->caseManagement = $this->getMockBuilder(CaseManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByOrderId'])
            ->getMockForAbstractClass();

        $this->updatingFactory = $this->getMockBuilder(UpdatingServiceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['cancelGuarantee'])
            ->getMock();

        $this->guaranteeAbility = $this->getMockBuilder(CancelGuaranteeAbility::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAvailable'])
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        $this->service = new CancelingService(
            $this->caseManagement,
            $this->updatingFactory,
            $this->gateway,
            $this->guaranteeAbility,
            $this->logger
        );
    }

    /**
     * Checks a test case, when validation for a guarantee is failed.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CancelingService::cancelForOrder
     */
    public function testCancelForOrderWithUnavailableDisposition()
    {
        $this->guaranteeAbility->expects(self::once())
            ->method('isAvailable')
            ->with(self::equalTo(self::$orderId))
            ->willReturn(false);

        $this->caseManagement->expects(self::never())
            ->method('getByOrderId');

        $this->gateway->expects(self::never())
            ->method('cancelGuarantee');

        $this->logger->expects(self::never())
            ->method('error');

        $this->updatingFactory->expects(self::never())
            ->method('create');

        $result = $this->service->cancelForOrder(self::$orderId);
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when request to Signifyd API fails.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CancelingService::cancelForOrder
     */
    public function testCancelForOrderWithFailedRequest()
    {
        $this->withCaseEntity();

        $this->gateway->expects(self::once())
            ->method('cancelGuarantee')
            ->with(self::equalTo(self::$caseId))
            ->willThrowException(new GatewayException('Something wrong.'));

        $this->logger->expects(self::once())
            ->method('error')
            ->with(self::equalTo('Something wrong.'));

        $this->updatingFactory->expects(self::never())
            ->method('create');

        $result = $this->service->cancelForOrder(self::$orderId);
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when request to Signifyd successfully processed and case entity has been updated.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CancelingService::cancelForOrder
     */
    public function testCancelForOrder()
    {
        $case = $this->withCaseEntity();

        $this->gateway->expects(self::once())
            ->method('cancelGuarantee')
            ->with(self::equalTo(self::$caseId))
            ->willReturn(CaseInterface::GUARANTEE_CANCELED);

        $this->logger->expects(self::never())
            ->method('error');

        $service = $this->getMockBuilder(StubUpdatingService::class)
            ->setMethods(['update'])
            ->getMock();
        $this->updatingFactory->expects(self::once())
            ->method('create')
            ->willReturn($service);

        $service->expects(self::once())
            ->method('update')
            ->with(self::equalTo($case), self::equalTo(['guaranteeDisposition' => CaseInterface::GUARANTEE_CANCELED]));

        $result = $this->service->cancelForOrder(self::$orderId);
        self::assertTrue($result);
    }

    /**
     * Gets mock for a case entity.
     *
     * @return CaseInterface|MockObject
     */
    private function withCaseEntity()
    {
        $this->guaranteeAbility->expects(self::once())
            ->method('isAvailable')
            ->with(self::equalTo(self::$orderId))
            ->willReturn(true);

        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCaseId'])
            ->getMockForAbstractClass();

        $this->caseManagement->expects(self::once())
            ->method('getByOrderId')
            ->with(self::equalTo(self::$orderId))
            ->willReturn($caseEntity);

        $caseEntity->expects(self::once())
            ->method('getCaseId')
            ->willReturn(self::$caseId);
        return $caseEntity;
    }
}
