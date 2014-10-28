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
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;

class StockItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem
     */
    protected $model;

    /**
     * @var QuoteItemQtyList| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemQtyList;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeConfig;

    protected function setUp()
    {
        $this->quoteItemQtyList = $this
            ->getMockBuilder('Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList')
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeConfig = $this
            ->getMockBuilder('Magento\Catalog\Model\ProductTypes\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->model = $objectManagerHelper->getObject(
            'Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem',
            [
                'quoteItemQtyList' => $this->quoteItemQtyList,
                'typeConfig' => $this->typeConfig
            ]
        );
    }

    public function testInitializeWithSubitem()
    {
        $qty = 2;
        $parentItemQty = 3;

        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->setMethods(
                [
                    'checkQuoteItemQty',
                    'setProductName',
                    'setIsChildItem',
                    'hasIsChildItem',
                    'unsIsChildItem',
                    '__wakeup'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItem = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->setMethods(
                [
                    'getParentItem',
                    'getProduct',
                    'getId',
                    'getQuoteId',
                    'setIsQtyDecimal',
                    'setData',
                    'setUseOldQty',
                    'setMessage',
                    'setBackorders',
                    '__wakeup'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $parentItem = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->setMethods(['getQty', 'setIsQtyDecimal', 'getProduct', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $parentProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeInstance = $this->getMockBuilder('Magento\Catalog\Model\Product\Type\AbstractType')
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeCustomOption = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(
                [
                    'getItemIsQtyDecimal',
                    'getHasQtyOptionUpdate',
                    'getOrigQty',
                    'getItemUseOldQty',
                    'getMessage',
                    'getItemBackorders',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $quoteItem->expects($this->any())->method('getParentItem')->will($this->returnValue($parentItem));
        $parentItem->expects($this->once())->method('getQty')->will($this->returnValue($parentItemQty));
        $quoteItem->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        $product->expects($this->any())->method('getId')->will($this->returnValue('product_id'));
        $quoteItem->expects($this->once())->method('getId')->will($this->returnValue('quote_item_id'));
        $quoteItem->expects($this->once())->method('getQuoteId')->will($this->returnValue('quote_id'));
        $this->quoteItemQtyList->expects($this->any())
            ->method('getQty')
            ->with('product_id', 'quote_item_id', 'quote_id', 0)
            ->will($this->returnValue('summary_qty'));
        $stockItem->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with($parentItemQty * $qty, 'summary_qty', $qty)
            ->will($this->returnValue($result));
        $product->expects($this->once())
            ->method('getCustomOption')
            ->with('product_type')
            ->will($this->returnValue($productTypeCustomOption));
        $productTypeCustomOption->expects($this->once())
            ->method('getValue')
            ->will(($this->returnValue('option_value')));
        $this->typeConfig->expects($this->once())
            ->method('isProductSet')
            ->with('option_value')
            ->will($this->returnValue(true));
        $product->expects($this->once())->method('getName')->will($this->returnValue('product_name'));
        $stockItem->expects($this->once())->method('setProductName')->with('product_name')->will($this->returnSelf());
        $stockItem->expects($this->once())->method('setIsChildItem')->with(true)->will($this->returnSelf());
        $stockItem->expects($this->once())->method('hasIsChildItem')->will($this->returnValue(true));
        $stockItem->expects($this->once())->method('unsIsChildItem');
        $result->expects($this->exactly(3))->method('getItemIsQtyDecimal')->will($this->returnValue(true));
        $quoteItem->expects($this->once())->method('setIsQtyDecimal')->with(true)->will($this->returnSelf());
        $parentItem->expects($this->once())->method('setIsQtyDecimal')->with(true)->will($this->returnSelf());
        $parentItem->expects($this->any())->method('getProduct')->will($this->returnValue($parentProduct));
        $result->expects($this->once())->method('getHasQtyOptionUpdate')->will($this->returnValue(true));
        $parentProduct->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($productTypeInstance));
        $productTypeInstance->expects($this->once())
            ->method('getForceChildItemQtyChanges')
            ->with($product)->will($this->returnValue(true));
        $result->expects($this->once())->method('getOrigQty')->will($this->returnValue('orig_qty'));
        $quoteItem->expects($this->once())->method('setData')->with('qty', 'orig_qty')->will($this->returnSelf());
        $result->expects($this->exactly(2))->method('getItemUseOldQty')->will($this->returnValue('item'));
        $quoteItem->expects($this->once())->method('setUseOldQty')->with('item')->will($this->returnSelf());
        $result->expects($this->exactly(2))->method('getMessage')->will($this->returnValue('message'));
        $quoteItem->expects($this->once())->method('setMessage')->with('message')->will($this->returnSelf());
        $result->expects($this->exactly(2))->method('getItemBackorders')->will($this->returnValue('backorders'));
        $quoteItem->expects($this->once())->method('setBackorders')->with('backorders')->will($this->returnSelf());

        $this->model->initialize($stockItem, $quoteItem, $qty);
    }

    public function testInitializeWithoutSubitem()
    {
        $qty = 3;

        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->setMethods(['checkQuoteItemQty', 'setProductName', 'setIsChildItem', 'hasIsChildItem', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItem = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->setMethods(['getProduct', 'getParentItem', 'getQtyToAdd', 'getId', 'getQuoteId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeCustomOption = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(
                ['getItemIsQtyDecimal', 'getHasQtyOptionUpdate', 'getItemUseOldQty', 'getMessage', 'getItemBackorders']
            )
            ->disableOriginalConstructor()
            ->getMock();

        $quoteItem->expects($this->once())->method('getParentItem')->will($this->returnValue(false));
        $quoteItem->expects($this->once())->method('getQtyToAdd')->will($this->returnValue(false));
        $product->expects($this->once())->method('getId')->will($this->returnValue('product_id'));
        $quoteItem->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        $quoteItem->expects($this->once())->method('getId')->will($this->returnValue('quote_item_id'));
        $quoteItem->expects($this->once())->method('getQuoteId')->will($this->returnValue('quote_id'));
        $this->quoteItemQtyList->expects($this->any())
            ->method('getQty')
            ->with('product_id', 'quote_item_id', 'quote_id', $qty)
            ->will($this->returnValue('summary_qty'));
        $stockItem->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with($qty, 'summary_qty', $qty)
            ->will($this->returnValue($result));
        $product->expects($this->once())
            ->method('getCustomOption')
            ->with('product_type')
            ->will($this->returnValue($productTypeCustomOption));
        $productTypeCustomOption->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('option_value'));
        $this->typeConfig->expects($this->once())
            ->method('isProductSet')
            ->with('option_value')
            ->will($this->returnValue(true));
        $product->expects($this->once())->method('getName')->will($this->returnValue('product_name'));
        $stockItem->expects($this->once())->method('setProductName')->with('product_name')->will($this->returnSelf());
        $stockItem->expects($this->once())->method('setIsChildItem')->with(true)->will($this->returnSelf());
        $stockItem->expects($this->once())->method('hasIsChildItem')->will($this->returnValue(false));
        $result->expects($this->once())->method('getItemIsQtyDecimal')->will($this->returnValue(null));
        $result->expects($this->once())->method('getHasQtyOptionUpdate')->will($this->returnValue(false));
        $result->expects($this->once())->method('getItemUseOldQty')->will($this->returnValue(null));
        $result->expects($this->once())->method('getMessage')->will($this->returnValue(null));
        $result->expects($this->once())->method('getItemBackorders')->will($this->returnValue(null));

        $this->model->initialize($stockItem, $quoteItem, $qty);
    }
}
