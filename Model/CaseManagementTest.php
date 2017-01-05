<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CaseManagement;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Contains test methods for case management service
 */
class CaseManagementTest extends \PHPUnit_Framework_TestCase
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

        static::assertNotEmpty($case->getEntityId());
        static::assertEquals(CaseInterface::STATUS_PENDING, $case->getStatus());
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseManagement::getByOrderId
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testGetByOrderId()
    {
        $order = $this->getOrder();
        $case = $this->caseManagement->getByOrderId($order->getEntityId());

        static::assertEquals(CaseInterface::GUARANTEE_PENDING, $case->getGuaranteeDisposition());
        static::assertEquals(CaseInterface::STATUS_PROCESSING, $case->getStatus());
        static::assertEquals(CaseInterface::DISPOSITION_GOOD, $case->getReviewDisposition());
        static::assertEquals('2016-12-12 15:17:17', $case->getCreatedAt());
        static::assertEquals('2016-12-12 19:23:16', $case->getUpdatedAt());
    }

    /**
     * Get stored order
     * @return OrderInterface
     */
    private function getOrder()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                ->setValue('100000001')
                ->create()
        ];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        return array_pop($orders);
    }
}
