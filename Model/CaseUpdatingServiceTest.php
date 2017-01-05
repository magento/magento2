<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\MessageGenerators\CaseCreation;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Contains tests for case entity updating service.
 */
class CaseUpdatingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CaseUpdatingService
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $messageGenerator = $this->objectManager->create(CaseCreation::class);

        $this->service = $this->objectManager->create(CaseUpdatingService::class, [
            'messageGenerator' => $messageGenerator
        ]);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseUpdatingService::update
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testUpdate()
    {
        $caseId = 123;
        $data = new DataObject(
            [
                'caseId' => $caseId,
                'score' => 750,
                'reviewDisposition' => CaseInterface::DISPOSITION_FRAUDULENT,
                'associatedTeam' => [
                    'teamName' => 'AnyTeam',
                    'teamId' => 26,
                    'getAutoDismiss' => true,
                    'getTeamDismissalDays' => 2
                ],
                'createdAt' => '2017-01-05T14:23:26-0800',
                'updatedAt' => '2017-01-05T14:44:26-0800',
                'guaranteeDisposition' => CaseInterface::GUARANTEE_APPROVED
            ]
        );

        $this->service->update($data);

        /** @var CaseRepositoryInterface $caseManagement */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId($caseId);

        static::assertNotEmpty($caseEntity);
        static::assertEquals('2017-01-05 22:23:26', $caseEntity->getCreatedAt());
        static::assertEquals(CaseInterface::GUARANTEE_APPROVED, $caseEntity->getGuaranteeDisposition());
        static::assertEquals('AnyTeam', $caseEntity->getAssociatedTeam()['teamName']);
        static::assertEquals(true, $caseEntity->isGuaranteeEligible());
        static::assertEquals(CaseInterface::STATUS_PROCESSING, $caseEntity->getStatus());

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($caseEntity->getOrderId());
        $histories = $order->getStatusHistories();
        static::assertNotEmpty($histories);

        /** @var OrderStatusHistoryInterface $caseCreationComment */
        $caseCreationComment = array_pop($histories);
        static::assertInstanceOf(OrderStatusHistoryInterface::class, $caseCreationComment);
        static::assertEquals("Signifyd Case $caseId has been created for order.", $caseCreationComment->getComment());
    }
}
