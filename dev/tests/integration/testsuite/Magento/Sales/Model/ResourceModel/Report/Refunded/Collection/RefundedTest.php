<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Refunded\Collection;

/**
 * Integration tests for refunds reports collection.
 */
class RefundedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Refunded\Collection\Refunded
     */
    private $_collection;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\ResourceModel\Report\Refunded\Collection\Refunded::class
        );
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter([1]);
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
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $creditMemoCreatedAt = $order->getCreditmemosCollection()->getFirstItem()->getCreatedAt();
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Stdlib\DateTime\DateTimeFactory::class
        )
            ->create();
        $creditmemoCreatedAtDate = $dateTime->date('Y-m-d', $creditMemoCreatedAt);

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
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
