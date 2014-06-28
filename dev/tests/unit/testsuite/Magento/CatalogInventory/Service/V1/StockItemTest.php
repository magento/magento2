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

use Magento\CatalogInventory\Model\Stock\ItemRegistry;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;

/**
 * Class StockItemTest
 */
class StockItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockItem
     */
    protected $model;

    /**
     * @var ItemRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemRegistry;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var Data\StockItemBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemBuilder;

    protected function setUp()
    {
        $this->stockItemRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\ItemRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder('Magento\Catalog\Model\ProductTypes\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemBuilder = $this->getMockBuilder(
            'Magento\CatalogInventory\Service\V1\Data\StockItemBuilder'
        )->disableOriginalConstructor()->getMock();

        $this->model = new StockItem($this->stockItemRegistry, $this->config, $this->stockItemBuilder);
    }

    public function testGetStockItem()
    {
        $productId = 123;
        $stockItemData = ['some_key' => 'someValue'];

        $stockItemModel = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $stockItemModel->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($stockItemData));

        $this->stockItemRegistry->expects($this->once())
            ->method('retrieve')
            ->with($productId)
            ->will($this->returnValue($stockItemModel));

        $this->stockItemBuilder->expects($this->once())
            ->method('populateWithArray')
            ->with($stockItemData);

        $stockItemDo = $this->getMockBuilder('Magento\CatalogInventory\Service\V1\Data\StockItem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemBuilder->expects($this->once())
            ->method('create')
            ->will($this->returnValue($stockItemDo));

        $this->assertEquals($stockItemDo, $this->model->getStockItem($productId));
    }

    public function testSaveStockItem()
    {
        $productId = 123;
        $stockItemData = ['some_key' => 'someValue'];

        $stockItemDo = $this->getMockBuilder('Magento\CatalogInventory\Service\V1\Data\StockItem')
            ->disableOriginalConstructor()
            ->getMock();
        $stockItemDo->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue($productId));
        $stockItemDo->expects($this->once())
            ->method('__toArray')
            ->will($this->returnValue($stockItemData));

        $stockItemModel = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $stockItemModel->expects($this->once())
            ->method('setData')
            ->with($stockItemData);
        $stockItemModel->expects($this->once())
            ->method('save');

        $this->stockItemRegistry->expects($this->once())
            ->method('retrieve')
            ->with($productId)
            ->will($this->returnValue($stockItemModel));

        $this->assertEquals($this->model, $this->model->saveStockItem($stockItemDo));
    }

    public function testSubtractQty()
    {
        $productId = 123;
        $qty = 1.5;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('subtractQty')
            ->with($qty);

        $this->assertEquals($this->model, $this->model->subtractQty($productId, $qty));
    }

    public function testCanSubtractQty()
    {
        $productId = 23;
        $result = false;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('canSubtractQty')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->canSubtractQty($productId));
    }

    public function testAddQty()
    {
        $productId = 143;
        $qty = 3.5;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('addQty')
            ->with($qty);

        $this->assertEquals($this->model, $this->model->addQty($productId, $qty));
    }

    public function testGetMinQty()
    {
        $productId = 53;
        $result = 3;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getMinQty')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getMinQty($productId));
    }

    public function testGetMinSaleQty()
    {
        $productId = 51;
        $result = 2;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getMinSaleQty')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getMinSaleQty($productId));
    }

    public function testGetMaxSaleQty()
    {
        $productId = 46;
        $result = 15;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getMaxSaleQty')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getMaxSaleQty($productId));
    }

    public function testGetNotifyStockQty()
    {
        $productId = 12;
        $result = 15.3;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getNotifyStockQty')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getNotifyStockQty($productId));
    }

    public function testEnableQtyIncrements()
    {
        $productId = 48;
        $result = true;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getEnableQtyIncrements')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getEnableQtyIncrements($productId));
    }

    public function testGetQtyIncrements()
    {
        $productId = 25;
        $result = 15;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getQtyIncrements')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getQtyIncrements($productId));
    }

    public function testGetBackorders()
    {
        $productId = 34;
        $result = 2;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getBackorders')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getBackorders($productId));
    }

    public function testGetManageStock()
    {
        $productId = 32;
        $result = 3;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getManageStock')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getManageStock($productId));
    }

    public function testGetCanBackInStock()
    {
        $productId = 59;
        $result = false;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getCanBackInStock')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getCanBackInStock($productId));
    }

    public function testCheckQty()
    {
        $productId = 143;
        $qty = 3.5;
        $result = false;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('checkQty')
            ->with($qty)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->checkQty($productId, $qty));
    }

    public function testSuggestQty()
    {
        $productId = 143;
        $qty = 3.5;
        $result = true;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('suggestQty')
            ->with($qty)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->suggestQty($productId, $qty));
    }

    public function testCheckQuoteItemQty()
    {
        $productId = 143;
        $qty = 3.5;
        $summaryQty = 4;
        $origQty = 1;
        $result = $this->getMock('Magento\Framework\Object');

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with($qty, $summaryQty, $origQty)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->checkQuoteItemQty($productId, $qty, $summaryQty, $origQty));
    }

    public function testVerifyStock()
    {
        $productId = 143;
        $qty = 2.5;
        $result = true;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('verifyStock')
            ->with($qty)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->verifyStock($productId, $qty));
    }

    public function testVerifyNotification()
    {
        $productId = 42;
        $qty = 7.3;
        $result = true;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('verifyNotification')
            ->with($qty)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->verifyNotification($productId, $qty));
    }

    public function testGetIsInStock()
    {
        $productId = 96;
        $result = false;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getIsInStock')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getIsInStock($productId));
    }

    public function testGetStockQty()
    {
        $productId = 34;
        $result = 3;

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('getStockQty')
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->getStockQty($productId));
    }

    public function testCheckQtyIncrements()
    {
        $productId = 86;
        $qty = 6;
        $result = $this->getMock('Magento\Framework\Object');

        $stockItemModel = $this->getStockItemModel($productId);
        $stockItemModel->expects($this->once())
            ->method('checkQtyIncrements')
            ->with($qty)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->checkQtyIncrements($productId, $qty));
    }

    public function testIsQty()
    {
        $configAll = [
            1 => ['is_qty' => true],
            2 => ['is_qty' => false],
            3 => []
        ];
        $this->config->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue($configAll));

        $this->assertTrue($this->model->isQty(1));
        $this->assertFalse($this->model->isQty(2));
        $this->assertFalse($this->model->isQty(3));
        $this->assertFalse($this->model->isQty(4));
    }

    public function testGetIsQtyTypeIds()
    {
        $configAll = [
            1 => ['is_qty' => true],
            2 => ['is_qty' => false],
            3 => []
        ];
        $resultAll = [1 => true, 2 => false, 3 => false];
        $resultTrue = [1 => true];
        $resultFalse = [2 => false, 3 => false];

        $this->config->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue($configAll));

        $this->assertEquals($resultAll, $this->model->getIsQtyTypeIds());
        $this->assertEquals($resultTrue, $this->model->getIsQtyTypeIds(true));
        $this->assertEquals($resultFalse, $this->model->getIsQtyTypeIds(false));
    }

    /**
     * @param int $productId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStockItemModel($productId)
    {
        $stockItemModel = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemRegistry->expects($this->once())
            ->method('retrieve')
            ->with($productId)
            ->will($this->returnValue($stockItemModel));

        return $stockItemModel;
    }
}
