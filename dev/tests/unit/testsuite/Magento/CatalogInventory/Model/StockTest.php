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
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Resource\Stock\Item\CollectionFactory;

/**
 * Class StockTest
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Stock
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatus;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    protected function setUp()
    {
        $this->collectionFactory = $this
            ->getMockBuilder('Magento\CatalogInventory\Model\Resource\Stock\Item\CollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockStatus = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Status')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemService = $this->getMockBuilder('Magento\CatalogInventory\Service\V1\StockItemService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemFactory = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\ItemFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\CatalogInventory\Model\Stock',
            [
                'stockStatus' => $this->stockStatus,
                'collectionFactory' => $this->collectionFactory,
                'stockItemService' => $this->stockItemService,
                'stockItemFactory' => $this->stockItemFactory,
                'productFactory' => $this->productFactory
            ]
        );
    }

    public function testAddItemsToProducts()
    {
        $storeId = 3;
        $productOneId = 1;
        $productOneStatus = \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK;
        $productTwoId = 2;
        $productThreeId = 3;

        $stockItemProductId = $productOneId;
        $stockItemStockId = \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID;

        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getIterator'])
            ->getMock();

        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem->expects($this->atLeastOnce())
            ->method('getProductId')
            ->will($this->returnValue($stockItemProductId));
        $stockItem->expects($this->atLeastOnce())
            ->method('getStockId')
            ->will($this->returnValue($stockItemStockId));

        $itemCollection = $this->getMockBuilder('Magento\CatalogInventory\Model\Resource\Stock\Item\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $itemCollection->expects($this->atLeastOnce())
            ->method('addStockFilter')
            ->with(Stock::DEFAULT_STOCK_ID)
            ->will($this->returnSelf());
        $itemCollection->expects($this->atLeastOnce())
            ->method('addProductsFilter')
            ->with($productCollection)
            ->will($this->returnSelf());
        $itemCollection->expects($this->atLeastOnce())
            ->method('joinStockStatus')
            ->with($storeId)
            ->will($this->returnSelf());
        $itemCollection->expects($this->atLeastOnce())
            ->method('load')
            ->will($this->returnValue([$stockItem]));

        $this->collectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($itemCollection));

        $productOne = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStockStatus', '__wakeup'])
            ->getMock();
        $productOne->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue($productOneId));
        $productOne->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->will($this->returnValue($productOneStatus));
        $productTwo = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $productTwo->expects($this->atLeastOnce())->method('getId')->will($this->returnValue($productTwoId));
        $productThree = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $productThree->expects($this->atLeastOnce())->method('getId')->will($this->returnValue($productThreeId));

        $productCollection->expects($this->atLeastOnce())->method('getStoreId')->will($this->returnValue($storeId));
        $productCollection->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$productOne, $productTwo, $productThree])));

        $this->stockStatus->expects($this->once())
            ->method('assignProduct')
            ->with($productOne, $stockItemStockId, $productOneStatus);

        $this->assertEquals($this->model, $this->model->addItemsToProducts($productCollection));
    }

    public function testBackItemQty()
    {
        $productId = 1;
        $qty = 1;
        $productType = 'simple';

        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->setMethods(
                [
                    'loadByProduct',
                    'getId',
                    'getCanBackInStock',
                    'getQty',
                    'getMinQty',
                    'setIsInStock',
                    'setStockStatusChangedAutomaticallyFlag',
                    'save',
                    '__wakeup',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $stockItem->expects($this->atLeastOnce())->method('loadByProduct')->with($productId)->will($this->returnSelf());
        $stockItem->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->stockItemFactory->expects($this->atLeastOnce())->method('create')->will($this->returnValue($stockItem));
        $this->getProductType($productId, $productType);
        $this->stockItemService->expects($this->once())
            ->method('isQty')
            ->with($productType)
            ->will($this->returnValue(true));
        $stockItem->expects($this->once())->method('getCanBackInStock')->will($this->returnValue(true));
        $stockItem->expects($this->once())->method('getQty')->will($this->returnValue('10'));
        $stockItem->expects($this->any())->method('getMinQty')->will($this->returnValue('3'));
        $stockItem->expects($this->once())->method('setIsInStock')->will($this->returnSelf());
        $stockItem->expects($this->once())->method('setStockStatusChangedAutomaticallyFlag')->will($this->returnSelf());
        $stockItem->expects($this->once())->method('save');

        $this->assertEquals($this->model, $this->model->backItemQty($productId, $qty));
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage We cannot specify a product identifier for the order item.
     */
    public function testRegisterItemSaleException()
    {
        $item = $this->getMockBuilder('\Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->registerItemSale($item);
    }

    public function testRegisterItemSale()
    {
        $productId = 1;
        $qty = 3;
        $productType = 'simple';
        $storeId = 1;

        $item = $this->getMockBuilder('\Magento\Framework\Object')
            ->setMethods(['getProductId', 'getStoreId', 'getQtyOrdered'])
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->setMethods(['loadByProduct', 'setStoreId', 'checkQty', 'subtractQty', 'save', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $item->expects($this->once())->method('getProductId')->will($this->returnValue($productId));
        $stockItem->expects($this->any())->method('loadByProduct')->with($productId)->will($this->returnSelf());
        $this->stockItemFactory->expects($this->any())->method('create')->will($this->returnValue($stockItem));
        $this->getProductType($productId, $productType);
        $this->stockItemService->expects($this->once())
            ->method('isQty')
            ->with($productType)
            ->will($this->returnValue(true));
        $item->expects($this->exactly(2))->method('getStoreId')->will($this->returnValue($storeId));
        $stockItem->expects($this->once())->method('setStoreId')->with($storeId)->will($this->returnSelf());
        $item->expects($this->exactly(2))->method('getQtyOrdered')->will($this->returnValue($qty));
        $stockItem->expects($this->once())->method('checkQty')->with($qty)->will($this->returnValue(true));
        $stockItem->expects($this->once())->method('subtractQty')->with($qty)->will($this->returnSelf());
        $stockItem->expects($this->once())->method('save')->will($this->returnSelf());;

        $this->assertEquals($this->model, $this->model->registerItemSale($item));
    }

    /**
     * @param $productId
     * @param $productType
     */
    private function getProductType($productId, $productType)
    {
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->any())->method('load')->with($productId);
        $product->expects($this->any())->method('getTypeId')->will($this->returnValue($productType));
        $this->productFactory->expects($this->any())->method('create')->will($this->returnValue($product));
    }
}
