<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Resource\Report;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Resource\Report\Collection
     */
    private $_collection;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_collection = $objectManager->create('Magento\Tax\Model\Resource\Report\Collection');
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter(array(1));
    }

    /**
     * @magentoDataFixture Magento/Tax/_files/order_with_tax.php
     * @magentoDataFixture Magento/Tax/_files/report_tax.php
     */
    public function testGetItems()
    {
        $expectedResult = array(
            array('code' => 'tax_code', 'percent' => 10, 'orders_count' => 1, 'tax_base_amount_sum' => 20)
        );
        $actualResult = array();
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
