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

/**
 * Class ObserverTest
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatus;

    /**
     * @var \Magento\CatalogInventory\Model\StockFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockFactory;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserver;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\CatalogInventory\Helper\Data |\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogInventoryData;

    /**
     * @var \Magento\CatalogInventory\Model\Stock | \PHPUnit_Framework_MockObject_MockObject
     */
    private $stock;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor | \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockIndexProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor | \PHPUnit_Framework_MockObject_MockObject
     */
    private $priceIndexer;

    protected function setUp()
    {
        $this->stockItemRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\ItemRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatus = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockFactory = $this->getMockBuilder('Magento\CatalogInventory\Model\StockFactory')
            ->setMethods(['create'])
            ->getMock();

        $this->catalogInventoryData = $this->getMock('Magento\CatalogInventory\Helper\Data', [], [], '', false);
        $this->stock = $this->getMock('Magento\CatalogInventory\Model\Stock', [], [], '', false);
        $this->stockIndexProcessor = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Stock\Processor',
            [],
            [],
            '',
            false
        );
        $this->priceIndexer = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Price\Processor',
            [],
            [],
            '',
            false
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\CatalogInventory\Model\Observer',
            [
                'stockItemRegistry' => $this->stockItemRegistry,
                'stockStatus' => $this->stockStatus,
                'stockFactory' => $this->stockFactory
            ]
        );

        $this->event = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getCollection', 'getCreditmemo'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
    }

    public function testAddInventoryData()
    {
        $productId = 4;
        $stockId = 6;
        $stockStatus = true;

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStockStatus', '__wakeup'])
            ->getMock();
        $product->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $product->expects($this->once())
            ->method('getStockStatus')
            ->will($this->returnValue($stockStatus));

        $this->event->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem->expects($this->once())
            ->method('getStockId')
            ->will($this->returnValue($stockId));

        $this->stockItemRegistry->expects($this->once())
            ->method('retrieve')
            ->with($productId)
            ->will($this->returnValue($stockItem));

        $this->stockStatus->expects($this->once())
            ->method('assignProduct')
            ->with($product, $stockId, $stockStatus)
            ->will($this->returnSelf());

        $this->assertEquals($this->model, $this->model->addInventoryData($this->eventObserver));
    }

    public function testAddStockStatusToCollection()
    {
        $requireStockItems = false;

        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['hasFlag'])
            ->getMock();
        $this->event->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($productCollection));

        $productCollection->expects($this->once())
            ->method('hasFlag')
            ->with('require_stock_items')
            ->will($this->returnValue($requireStockItems));

        $this->stockStatus->expects($this->once())
            ->method('addStockStatusToProducts')
            ->with($productCollection)
            ->will($this->returnSelf());

        $this->assertEquals($this->model, $this->model->addStockStatusToCollection($this->eventObserver));
    }

    public function testAddStockStatusToCollectionRequireStockItems()
    {
        $requireStockItems = true;

        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['hasFlag'])
            ->getMock();
        $this->event->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($productCollection));

        $productCollection->expects($this->once())
            ->method('hasFlag')
            ->with('require_stock_items')
            ->will($this->returnValue($requireStockItems));

        $stock = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($stock));

        $stock->expects($this->once())
            ->method('addItemsToProducts')
            ->with($productCollection)
            ->will($this->returnSelf());

        $this->assertEquals($this->model, $this->model->addStockStatusToCollection($this->eventObserver));
    }

    public function refundOrderInventory()
    {
        $ids = ['1', '14'];
        $items = [];
        foreach ($ids as $id) {
            $items[] = $this->getCreditMemoItem($id);
        }
        $creditMemo = $this->getMock('Magento\Sales\Model\Order\Creditmemo', [], [], '', false);
        $creditMemo->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue($items));

        $this->event->expects($this->once())
            ->method('getCreditmemo')
            ->will($this->returnValue($creditMemo));

        $this->catalogInventoryData->expects($this->once())
            ->method('isAutoReturnEnabled')
            ->will($this->returnValue(true));

        $this->stock->expects($this->once())
            ->method('revertProductsSale')
            ->with($items);
        $this->stockIndexProcessor->expects($this->once())
            ->method('reidexList')
            ->with($ids);

        $this->model->refundOrderInventory($this->eventObserver);
    }

    private function getCreditMemoItem($productId)
    {
        $item = $this->getMock('Magento\Sales\Model\Order\Creditmemo\Item', [], [], '', false);
        $item->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue($productId));
        return $item;
    }
}
