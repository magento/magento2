<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Contains test methods for case management service
 */
class CaseManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setup()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->caseManagement = $this->objectManager->get(CaseManagement::class);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseManagement::create
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreate()
    {
        $order = $this->getOrder();
        $case = $this->caseManagement->create($order->getEntityId());

        self::assertNotEmpty($case->getEntityId());
        self::assertEquals(CaseInterface::STATUS_PENDING, $case->getStatus());
        self::assertEquals(CaseInterface::GUARANTEE_PENDING, $case->getGuaranteeDisposition());
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseManagement::getByOrderId
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testGetByOrderId()
    {
        $order = $this->getOrder();
        $case = $this->caseManagement->getByOrderId($order->getEntityId());

        self::assertEquals(CaseInterface::STATUS_PROCESSING, $case->getStatus());
        self::assertEquals(CaseInterface::DISPOSITION_GOOD, $case->getReviewDisposition());
        self::assertEquals('2016-12-12 15:17:17', $case->getCreatedAt());
        self::assertEquals('2016-12-12 19:23:16', $case->getUpdatedAt());
    }

    /**
     * Get stored order
     * @return OrderInterface
     */
    private function getOrder()
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, '100000001')
            ->create();

        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        return array_pop($orders);
    }
}
