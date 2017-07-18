<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Refunded\Collection;

/**
 * Integration tests for refunds reports collection which is used to obtain refund reports by refund date.
 */
class RefundedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Refunded\Collection\Refunded
     */
    private $collection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->collection = $this->objectManager->create(
            \Magento\Sales\Model\ResourceModel\Report\Refunded\Collection\Refunded::class
        );
        $this->collection->setPeriod('day')
            ->setDateRange(null, null)
            ->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_info.php
     * @magentoDataFixture Magento/Sales/_files/order_from_past.php
     * @magentoDataFixture Magento/Sales/_files/report_refunded.php
     * @return void
     */
    public function testGetItems()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $creditmemoCreatedAt = $order->getCreditmemosCollection()->getFirstItem()->getCreatedAt();
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTimeFactory::class)
            ->create();
        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone */
        $timezone = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $creditmemoCreatedAt = $timezone->formatDateTime(
            $creditmemoCreatedAt,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null,
            'yyyy-MM-dd'
        );
        $creditmemoCreatedAtDate = $dateTime->date('Y-m-d', $creditmemoCreatedAt);

        $expectedResult = [
            [
                'orders_count' => 1,
                'refunded' => 50,
                'online_refunded' => 50,
                'offline_refunded' => 0,
                'period' => $creditmemoCreatedAtDate
            ],
        ];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
