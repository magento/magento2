<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Report\Shipping\Collection;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Report\Shipping\Collection\Order
     */
    private $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Resource\Report\Shipping\Collection\Order'
        );
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_shipping.php
     * @magentoDataFixture Magento/Sales/_files/report_shipping.php
     */
    public function testGetItems()
    {
        $expectedResult = [['orders_count' => 1, 'total_shipping' => 36, 'total_shipping_actual' => 34]];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
