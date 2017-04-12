<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Report;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\ResourceModel\Report\Collection
     */
    private $_collection;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_collection = $objectManager->create(\Magento\Tax\Model\ResourceModel\Report\Collection::class);
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Tax/_files/order_with_tax.php
     * @magentoDataFixture Magento/Tax/_files/report_tax.php
     */
    public function testGetItems()
    {
        $expectedResult = [
            ['code' => 'tax_code', 'percent' => 10, 'orders_count' => 1, 'tax_base_amount_sum' => 20],
        ];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
