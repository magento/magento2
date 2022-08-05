<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection;

/**
 * Integration tests for invoices reports collection which is used to obtain invoice reports by order date.
 */
class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection\Order
     */
    private $collection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->collection = $this->objectManager->create(
            \Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection\Order::class
        );
        $this->collection->setPeriod('day')
            ->setDateRange(null, null)
            ->addStoreFilter([1]);
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
        $order = $this->objectManager
            ->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTimeFactory::class)
            ->create();
        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone */
        $timezone = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $orderCreatedAt = $timezone->formatDateTime(
            $order->getCreatedAt(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null,
            'yyyy-MM-dd'
        );
        $invoiceCreatedAtDate = $dateTime->date('Y-m-d', $orderCreatedAt);

        $expectedResult = [
            [
                'orders_count' => 1,
                'orders_invoiced' => 1,
                'period' => $invoiceCreatedAtDate
            ],
        ];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->collection->getItems() as $reportItem) {
            $actualResult[] = [
                'orders_count' => $reportItem->getData('orders_count'),
                'orders_invoiced' => $reportItem->getData('orders_invoiced'),
                'period' => $reportItem->getData('period')
            ];
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
