<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Framework\App\ObjectManager;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
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
     * @covers \Magento\Signifyd\Model\Guarantee\CreationService::create
     */
    public function testCreateWithoutCaseEntity()
    {
        $orderId = 123;
        $this->logger->expects(self::once())
            ->method('error')
            ->with('Cannot find case entity for order entity id: 123');

        $this->gateway->expects(self::never())
            ->method('submitCaseForGuarantee');

        $result = $this->service->create($orderId);
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when request is failing.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CreationService::create
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testCreateWithFailedRequest()
    {
        $caseEntity = $this->getCaseEntity();

        $this->gateway->expects(self::once())
            ->method('submitCaseForGuarantee')
            ->willThrowException(new ApiCallException('Something wrong'));

        $this->logger->expects(self::once())
            ->method('error')
            ->with('Something wrong');

        $result = $this->service->create($caseEntity->getOrderId());
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when case entity updating is failed.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CreationService::create
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testCreateWithFailedCaseUpdating()
    {
        $caseEntity = $this->getCaseEntity();

        $this->gateway->expects(self::once())
            ->method('submitCaseForGuarantee')
            ->with($caseEntity->getCaseId())
            ->willReturn([]);

        $this->logger->expects(self::once())
            ->method('error')
            ->with('The "caseId" should not be empty.');

        $result = $this->service->create($caseEntity->getOrderId());
        self::assertFalse($result);
    }

    /**
     * Checks a test case, when case entity is updated successfully.
     *
     * @covers \Magento\Signifyd\Model\Guarantee\CreationService::create
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testCreate()
    {
        $caseEntity = $this->getCaseEntity();
        $data = [
            'caseId' => $caseEntity->getCaseId(),
            'guaranteeEligible' => true,
            'guaranteeDisposition' => CaseInterface::GUARANTEE_IN_REVIEW
        ];

        $this->gateway->expects(self::once())
            ->method('submitCaseForGuarantee')
            ->with($caseEntity->getCaseId())
            ->willReturn($data);

        $this->logger->expects(self::never())
            ->method('error');

        $result = $this->service->create($caseEntity->getOrderId());
        self::assertTrue($result);

        $updatedCase = $this->getCaseEntity();
        self::assertEquals(CaseInterface::GUARANTEE_IN_REVIEW, $updatedCase->getGuaranteeDisposition());
        self::assertTrue((bool) $updatedCase->isGuaranteeEligible());
        self::assertEquals(CaseInterface::STATUS_PROCESSING, $updatedCase->getStatus());
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
