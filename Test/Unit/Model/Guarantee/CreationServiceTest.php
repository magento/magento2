<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\Guarantee;

use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Signifyd\Model\Guarantee\CreationService;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Model\CaseServices\UpdatingServiceFactory;
use Magento\Signifyd\Model\CaseServices\UpdatingServiceInterface;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Psr\Log\LoggerInterface;
use Magento\Signifyd\Api\Data\CaseInterface;

class CreationServiceTest extends TestCase
{
    /**
     * @var CreationService|MockObject
     */
    private $service;

    /**
     * @var CaseManagementInterface|MockObject
     */
    private $caseManagement;

    /**
     * @var UpdatingServiceInterface|MockObject
     */
    private $caseUpdatingService;

    /**
     * @var Gateway|MockObject
     */
    private $gateway;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    public function setUp()
    {
        $this->caseManagement = $this->getMockBuilder(CaseManagementInterface::class)
            ->getMockForAbstractClass();

        $caseUpdatingServiceFactory = $this->getMockBuilder(UpdatingServiceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->caseUpdatingService = $this->getMockBuilder(UpdatingServiceInterface::class)
            ->getMockForAbstractClass();
        $caseUpdatingServiceFactory
            ->method('create')
            ->willReturn($this->caseUpdatingService);

        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->service = new CreationService(
            $this->caseManagement,
            $caseUpdatingServiceFactory,
            $this->gateway,
            $this->logger
        );
    }

    public function testCreateForOrderWithoutCase()
    {
        $dummyOrderId = 1;
        $this->withCaseEntityNotExistsForOrderId($dummyOrderId);

        $this->gateway
            ->expects($this->never())
            ->method('submitCaseForGuarantee');
        $this->caseUpdatingService
            ->expects($this->never())
            ->method('update');
        $this->setExpectedException(NotFoundException::class);

        $this->service->createForOrder($dummyOrderId);
    }

    public function testCreateForOrderWitCase()
    {
        $dummyOrderId = 1;
        $dummyCaseId = 42;
        $this->withCaseEntityExistsForOrderId(
            $dummyOrderId,
            [
                'caseId' => $dummyCaseId,
            ]
        );

        $this->gateway
            ->expects($this->once())
            ->method('submitCaseForGuarantee');

        $this->service->createForOrder($dummyOrderId);
    }

    public function testCreateForOrderWithGatewayFailure()
    {
        $dummyOrderId = 1;
        $dummyCaseId = 42;
        $dummyGatewayFailureMessage = 'Everything fails sometimes';
        $this->withCaseEntityExistsForOrderId(
            $dummyOrderId,
            [
                'caseId' => $dummyCaseId,
            ]
        );
        $this->withGatewayFailure($dummyGatewayFailureMessage);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->equalTo($dummyGatewayFailureMessage));
        $this->caseUpdatingService
            ->expects($this->never())
            ->method('update');

        $result = $this->service->createForOrder($dummyOrderId);
        $this->assertEquals(
            false,
            $result,
            'Service should return false in case of gateway failure'
        );
    }

    public function testCreateForOrderWithGatewaySuccess()
    {
        $dummyOrderId = 1;
        $dummyCaseId = 42;
        $dummyGuaranteeDisposition = 'foo';
        $this->withCaseEntityExistsForOrderId(
            $dummyOrderId,
            [
                'caseId' => $dummyCaseId,
            ]
        );
        $this->withGatewaySuccess($dummyGuaranteeDisposition);

        $this->caseUpdatingService
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo([
                'caseId' => $dummyCaseId,
                'guaranteeDisposition' => $dummyGuaranteeDisposition,
            ]));

        $this->service->createForOrder($dummyOrderId);
    }

    public function testCreateForOrderWithCaseUpdate()
    {
        $dummyOrderId = 1;
        $dummyCaseId = 42;
        $dummyGuaranteeDisposition = 'foo';
        $this->withCaseEntityExistsForOrderId(
            $dummyOrderId,
            [
                'caseId' => $dummyCaseId,
            ]
        );
        $this->withGatewaySuccess($dummyGuaranteeDisposition);



        $result = $this->service->createForOrder($dummyOrderId);
        $this->assertEquals(
            true,
            $result,
            'Service should return true in case if case update service is called'
        );
    }

    public function testCreateForOrderWithNotRegisteredCase()
    {
        $dummyOrderId = 1;
        $dummyCaseId = null;
        $this->withCaseEntityExistsForOrderId(
            $dummyOrderId,
            [
                'caseId' => $dummyCaseId,
            ]
        );

        $this->gateway
            ->expects($this->never())
            ->method('submitCaseForGuarantee');
        $this->caseUpdatingService
            ->expects($this->never())
            ->method('update');
        $this->setExpectedException(NotFoundException::class);

        $this->service->createForOrder($dummyOrderId);
    }

    public function testCreateForOrderWithExistedGuarantee()
    {
        $dummyOrderId = 1;
        $dummyCaseId = 42;
        $dummyGuarantyDisposition = 'APPROVED';
        $this->withCaseEntityExistsForOrderId(
            $dummyOrderId,
            [
                'caseId' => $dummyCaseId,
                'guaranteeDisposition' => $dummyGuarantyDisposition
            ]
        );

        $this->gateway
            ->expects($this->never())
            ->method('submitCaseForGuarantee');
        $this->caseUpdatingService
            ->expects($this->never())
            ->method('update');
        $this->setExpectedException(AlreadyExistsException::class);

        $this->service->createForOrder($dummyOrderId);
    }

    private function withCaseEntityNotExistsForOrderId($orderId)
    {
        $this->caseManagement
            ->method('getByOrderId')
            ->with($this->equalTo($orderId))
            ->willReturn(null);
    }

    private function withCaseEntityExistsForOrderId($orderId, array $caseData = [])
    {
        $dummyCaseEntity = $this->getMockBuilder(CaseInterface::class)
            ->getMockForAbstractClass();
        foreach ($caseData as $caseProperty => $casePropertyValue) {
            $dummyCaseEntity
                ->method('get' . ucfirst($caseProperty))
                ->willReturn($casePropertyValue);
        }

        $this->caseManagement
            ->method('getByOrderId')
            ->with($this->equalTo($orderId))
            ->willReturn($dummyCaseEntity);
    }

    private function withGatewayFailure($failureMessage)
    {
        $this->gateway
            ->method('submitCaseForGuarantee')
            ->willThrowException(new GatewayException($failureMessage));
    }

    private function withGatewaySuccess($gatewayResult)
    {
        $this->gateway
            ->method('submitCaseForGuarantee')
            ->willReturn($gatewayResult);
    }
}
