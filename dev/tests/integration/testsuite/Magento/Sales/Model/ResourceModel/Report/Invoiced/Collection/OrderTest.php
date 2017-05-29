<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection;

/**
 * Integration tests for invoices reports collection which is used to obtain invoice reports by order date.
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection\Order
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
            \Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection\Order::class
        );
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoDataFixture Magento/Sales/_files/order_from_past.php
     * @magentoDataFixture Magento/Sales/_files/report_invoiced.php
     * @return void
     */
    public function testGetItems()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Stdlib\DateTime\DateTimeFactory::class
        )
            ->create();
        $invoiceCreatedAtDate = $dateTime->date('Y-m-d', $order->getCreatedAt());

        $expectedResult = [
            [
                'orders_count' => 1,
                'orders_invoiced' => 1,
                'period' => $invoiceCreatedAtDate
            ],
        ];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = [
                'orders_count' => $reportItem->getData('orders_count'),
                'orders_invoiced' => $reportItem->getData('orders_invoiced'),
                'period' => $reportItem->getData('period')
            ];
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
