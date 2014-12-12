<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Resource\Report\Invoiced\Collection;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Report\Invoiced\Collection\Order
     */
    private $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Resource\Report\Invoiced\Collection\Order'
        );
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoDataFixture Magento/Sales/_files/report_invoiced.php
     */
    public function testGetItems()
    {
        $expectedResult = [['orders_count' => 1, 'orders_invoiced' => 1]];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = [
                'orders_count' => $reportItem->getData('orders_count'),
                'orders_invoiced' => $reportItem->getData('orders_invoiced'),
            ];
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
