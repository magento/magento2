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
namespace Magento\Catalog\Model\Rss\Product;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NotifyStockTest
 * @package Magento\Catalog\Model\Rss\Product
 */
class NotifyStockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Rss\Product\NotifyStock
     */
    protected $notifyStock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */

    protected $productFactory;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Model\Resource\StockFactory
     */
    protected $stockFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Model\Resource\Stock
     */
    protected $stock;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $status;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    protected function setUp()
    {
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create']);
        $this->productFactory->expects($this->any())->method('create')->will($this->returnValue($this->product));

        $this->stock = $this->getMock('Magento\CatalogInventory\Model\Resource\Stock', [], [], '', false);
        $this->stockFactory = $this->getMock('Magento\CatalogInventory\Model\Resource\StockFactory', ['create']);
        $this->stockFactory->expects($this->any())->method('create')->will($this->returnValue($this->stock));

        $this->status = $this->getMock('Magento\Catalog\Model\Product\Attribute\Source\Status');
        $this->eventManager = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->notifyStock = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Rss\Product\NotifyStock',
            [
                'productFactory' => $this->productFactory,
                'stockFactory' => $this->stockFactory,
                'productStatus' => $this->status,
                'eventManager' => $this->eventManager
            ]
        );
    }

    public function testGetProductsCollection()
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $productCollection */
        $productCollection = $this->getMock('Magento\Catalog\Model\Resource\Product\Collection', [], [], '', false);
        $this->product->expects($this->once())->method('getCollection')->will($this->returnValue($productCollection));

        $productCollection->expects($this->once())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('setOrder')->will($this->returnSelf());

        $this->eventManager->expects($this->once())->method('dispatch')->with(
            'rss_catalog_notify_stock_collection_select'
        );
        $this->stock->expects($this->once())->method('addLowStockFilter')->with($productCollection);

        $products = $this->notifyStock->getProductsCollection();
        $this->assertEquals($productCollection, $products);

    }
}
