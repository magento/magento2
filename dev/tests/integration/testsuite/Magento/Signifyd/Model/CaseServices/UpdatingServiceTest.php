<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\MessageGenerators\GeneratorFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Contains tests for case entity updating service.
 */
class UpdatingServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UpdatingService
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var GeneratorFactory $messageFactory */
        $messageFactory = $this->objectManager->get(GeneratorFactory::class);
        $messageGenerator = $messageFactory->create('cases/creation');

        $this->service = $this->objectManager->create(UpdatingService::class, [
            'messageGenerator' => $messageGenerator
        ]);
    }

    /**
     * Checks case updating flow and messages in order comments history.
     * Also checks that order is unholded when case guarantee disposition is APPROVED.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testUpdate()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId,
            'score' => 750,
            'orderId' => '100000001',
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
        ];

        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        /** @var CaseInterface $caseEntity */
        $caseEntity = $caseRepository->getByCaseId($caseId);

        $this->service->update($caseEntity, $data);

        $caseEntity = $caseRepository->getByCaseId($caseId);
        $orderEntityId = $caseEntity->getOrderId();
        $gridGuarantyStatus = $this->getOrderGridGuarantyStatus($orderEntityId);

        self::assertNotEmpty($caseEntity);
        self::assertEquals('2017-01-05 22:23:26', $caseEntity->getCreatedAt());
        self::assertEquals(CaseInterface::GUARANTEE_APPROVED, $caseEntity->getGuaranteeDisposition());
        self::assertEquals('AnyTeam', $caseEntity->getAssociatedTeam()['teamName']);
        self::assertEquals(true, $caseEntity->isGuaranteeEligible());
        self::assertEquals(CaseInterface::STATUS_PROCESSING, $caseEntity->getStatus());
        self::assertEquals($orderEntityId, $caseEntity->getOrderId());
        self::assertEquals(
            $gridGuarantyStatus,
            $caseEntity->getGuaranteeDisposition(),
            'Signifyd guaranty status in sales_order_grid table does not match case entity guaranty status'
        );

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($caseEntity->getOrderId());
        self::assertEquals(Order::STATE_PROCESSING, $order->getState());
        $histories = $order->getStatusHistories();
        self::assertNotEmpty($histories);

        /** @var OrderStatusHistoryInterface $caseCreationComment */
        $caseCreationComment = array_pop($histories);
        self::assertInstanceOf(OrderStatusHistoryInterface::class, $caseCreationComment);
        self::assertEquals("Signifyd Case $caseId has been created for order.", $caseCreationComment->getComment());
    }

    /**
     * Checks that order is holded when case guarantee disposition is DECLINED.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     * @magentoDataFixture Magento/Signifyd/_files/approved_case.php
     */
    public function testOrderStateAfterDeclinedGuaranteeDisposition()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId,
            'orderId' => '100000001',
            'guaranteeDisposition' => CaseInterface::GUARANTEE_DECLINED
        ];

        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId($caseId);

        $this->service->update($caseEntity, $data);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($caseEntity->getOrderId());

        self::assertEquals(Order::STATE_HOLDED, $order->getState());
    }

    /**
     * Checks that order doesn't become holded
     * when previous case guarantee disposition was DECLINED
     * and webhook without guarantee disposition was received.
     *
     * @covers \Magento\Signifyd\Model\CaseServices\UpdatingService::update
     * @magentoDataFixture Magento/Signifyd/_files/declined_case.php
     */
    public function testOrderStateAfterWebhookWithoutGuaranteeDisposition()
    {
        $caseId = 123;
        $data = [
            'caseId' => $caseId,
            'orderId' => '100000001'
        ];

        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId($caseId);

        $this->service->update($caseEntity, $data);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($caseEntity->getOrderId());

        self::assertEquals(Order::STATE_PROCESSING, $order->getState());
    }

    /**
     * Returns value of signifyd_guarantee_status column from sales order grid
     *
     * @param int $orderEntityId
     * @return string|null
     */
    private function getOrderGridGuarantyStatus($orderEntityId)
    {
        /** @var Collection $orderGridCollection */
        $orderGridCollection = $this->objectManager->get(Collection::class);

        $items = $orderGridCollection->addFilter($orderGridCollection->getIdFieldName(), $orderEntityId)
            ->getItems();
        $result = array_pop($items);

        return isset($result['signifyd_guarantee_status']) ? $result['signifyd_guarantee_status'] : null;
    }
}
