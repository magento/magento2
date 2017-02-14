<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\MessageGenerators\GeneratorFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Contains tests for case entity updating service.
 */
class UpdatingServiceTest extends \PHPUnit_Framework_TestCase
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

        static::assertNotEmpty($caseEntity);
        static::assertEquals('2017-01-05 22:23:26', $caseEntity->getCreatedAt());
        static::assertEquals(CaseInterface::GUARANTEE_APPROVED, $caseEntity->getGuaranteeDisposition());
        static::assertEquals('AnyTeam', $caseEntity->getAssociatedTeam()['teamName']);
        static::assertEquals(true, $caseEntity->isGuaranteeEligible());
        static::assertEquals(CaseInterface::STATUS_PROCESSING, $caseEntity->getStatus());
        static::assertEquals($orderEntityId, $caseEntity->getOrderId());
        static::assertEquals(
            $gridGuarantyStatus,
            $caseEntity->getGuaranteeDisposition(),
            'Signifyd guaranty status in sales_order_grid table does not match case entity guaranty status'
        );

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

    /**
     * Returns value of signifyd_guarantee_status column from sales order grid
     *
     * @param int $orderEntityId
     * @return string|null
     */
    private function getOrderGridGuarantyStatus($orderEntityId)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $orderGridCollection */
        $orderGridCollection = $this->objectManager->get(
            \Magento\Sales\Model\ResourceModel\Order\Grid\Collection::class
        );

        $items = $orderGridCollection->addFilter($orderGridCollection->getIdFieldName(), $orderEntityId)
            ->getItems();
        $result = array_pop($items);

        return isset($result['signifyd_guarantee_status']) ? $result['signifyd_guarantee_status'] : null;
    }
}
