<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Sales\Sales;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;

/**
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Creates and inits block
     *
     * @param string|null $reportType
     * @return \Magento\Reports\Block\Adminhtml\Sales\Sales\Grid
     */
    protected function _createBlock($reportType = null)
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Reports\Block\Adminhtml\Sales\Sales\Grid::class
        );

        $filterData = new \Magento\Framework\DataObject();
        if ($reportType) {
            $filterData->setReportType($reportType);
        }
        $block->setFilterData($filterData);

        return $block;
    }

    /**
     * @return string
     */
    public function testGetResourceCollectionNameNormal()
    {
        $block = $this->_createBlock();
        $normalCollection = $block->getResourceCollectionName();
        $this->assertTrue(class_exists($normalCollection));

        return $normalCollection;
    }

    /**
     * @param  string $normalCollection
     */
    #[Depends('testGetResourceCollectionNameNormal')]
    public function testGetResourceCollectionNameWithFilter($normalCollection)
    {
        $block = $this->_createBlock('updated_at_order');
        $filteredCollection = $block->getResourceCollectionName();
        $this->assertTrue(class_exists($filteredCollection));

        $this->assertNotEquals($normalCollection, $filteredCollection);
    }

    /**
     * Check that grid does not contain unnecessary totals row
     *
     * @param $from string
     * @param $to string
     * @param $expectedResult bool
     *
     * @magentoDataFixture Magento/Reports/_files/orders.php
     */
    #[DataProvider('getCountTotalsDataProvider')]
    public function testGetCountTotals($from, $to, $expectedResult)
    {
        $block = $this->_createBlock();
        $filterData = new \Magento\Framework\DataObject();

        $filterData->setReportType('updated_at_order');
        $filterData->setPeriodType('day');
        $filterData->setData('from', $from);
        $filterData->setData('to', $to);
        $block->setFilterData($filterData);

        $block->toHtml();
        $this->assertEquals($expectedResult, $block->getCountTotals());
    }

    /**
     * Data provider for testGetCountTotals
     *
     * @return array
     */
    public static function getCountTotalsDataProvider()
    {
        $time = time();
        return [
            [date("Y-m-d", $time + 48 * 60 * 60), date("Y-m-d", $time + 72 * 60 * 60), false],
            [date("Y-m-d", $time - 48 * 60 * 60), date("Y-m-d", $time + 48 * 60 * 60), true],
            [null, null, false],
        ];
    }
}
