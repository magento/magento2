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
namespace Magento\CatalogInventory\Service\V1;

use Magento\CatalogInventory\Model\Stock\Status;

/**
 * Test for Magento\CatalogInventory\Service\V1\StockStatusService
 */
class StockStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int[] $productIds
     * @param int $websiteId
     * @param int $stockId
     * @param [] $expectedResult
     * @dataProvider getProductStockStatusDataProvider
     */
    public function testGetProductStockStatus($productIds, $websiteId, $stockId, $expectedResult)
    {
        // 1 Create mocks
        $stockStatus = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $model = new StockStatusService($stockStatus);

        // 2. Set expectations
        $stockStatus->expects($this->once())
            ->method('getProductStockStatus')
            ->with($productIds, $websiteId, $stockId)
            ->will($this->returnValue($expectedResult));

        // 3. Run tested method
        $result = $model->getProductStockStatus($productIds, $websiteId, $stockId);

        // 5. Compare actual result with expected result
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getProductStockStatusDataProvider()
    {
        return [
            [[1,2], 3, 4, []],
        ];
    }
}
