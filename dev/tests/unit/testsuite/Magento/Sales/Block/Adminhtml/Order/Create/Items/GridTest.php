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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Items;

class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
     */
    protected $block;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Service\V1\StockItemService */
    protected $stockItemService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Block\Template
     */
    protected $priceRenderBlock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Layout
     */
    protected $layoutMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item  */
    protected $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Initialize required data
     */
    protected function setUp()
    {
        $orderCreateMock = $this->getMock('Magento\Sales\Model\AdminOrder\Create', ['__wakeup'], [], '', false);
        $taxData = $this->getMockBuilder('Magento\Tax\Helper\Data')->disableOriginalConstructor()->getMock();
        $this->priceCurrency = $this->getMockBuilder('\Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $coreData = $this->getMockBuilder('Magento\Core\Helper\Data')->disableOriginalConstructor()->getMock();
        $sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()
            ->setMethods(array('getQuote', '__wakeup'))
            ->getMock();

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(array('getStore', '__wakeup'))
            ->getMock();

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(array('__wakeup'))
            ->getMock();
        $this->priceCurrency->expects($this->any())
            ->method('convertAndFormat')
            ->will($this->returnArgument(0));
        $quoteMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $sessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $wishlistFactoryMock = $this->getMockBuilder('Magento\Wishlist\Model\WishlistFactory')
            ->setMethods(array('methods', '__wakeup'))
            ->getMock();

        $giftMessageSave = $this->getMockBuilder('Magento\Giftmessage\Model\Save')
            ->setMethods(array('__wakeup'))
            ->disableOriginalConstructor()
            ->getMock();

        $taxConfig = $this->getMockBuilder('Magento\Tax\Model\Config')->disableOriginalConstructor()->getMock();
        $this->stockItemService = $this->getMock(
            'Magento\CatalogInventory\Service\V1\StockItemService',
            [],
            [],
            '',
            false
        );

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $this->objectManager->getObject(
            'Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid',
            array(
                'wishlistFactory' => $wishlistFactoryMock,
                'giftMessageSave' => $giftMessageSave,
                'taxConfig' => $taxConfig,
                'taxData' => $taxData,
                'sessionQuote' => $sessionMock,
                'orderCreate' => $orderCreateMock,
                'priceCurrency' => $this->priceCurrency,
                'coreData' => $coreData,
                'stockItemService' => $this->stockItemService
            )
        );

        $this->priceRenderBlock = $this->getMockBuilder('\Magento\Backend\Block\Template')
            ->disableOriginalConstructor()
            ->setMethods(['setItem', 'toHtml'])
            ->getMock();

        $this->layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->itemMock = $this->getMockBuilder('\Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
    }

    /**
     * @param array $itemData
     * @param string $expectedMessage
     * @param string $productType
     * @dataProvider tierPriceDataProvider
     */
    public function testTierPriceInfo($itemData, $expectedMessage, $productType)
    {
        $itemMock = $this->prepareItem($itemData, $productType);
        $result = $this->block->getTierHtml($itemMock);
        $this->assertEquals($expectedMessage, $result);
    }

    /**
     * Provider for test
     *
     * @return array
     */
    public function tierPriceDataProvider()
    {
        return array(
            array(
                array(array('price' => 100, 'price_qty' => 1)),
                '1 with 100% discount each',
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ),
            array(
                array(array('price' => 100, 'price_qty' => 1), array('price' => 200, 'price_qty' => 2)),
                '1 with 100% discount each<br />2 with 200% discount each',
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ),
            array(
                array(array('price' => 50, 'price_qty' => 2)),
                '2 for 50',
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ),
            array(
                array(array('price' => 50, 'price_qty' => 2), array('price' => 150, 'price_qty' => 3)),
                '2 for 50<br />3 for 150',
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ),
            array(0, '', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        );
    }

    /**
     * @param array|int $tierPrices
     * @param string $productType
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item
     */
    protected function prepareItem($tierPrices, $productType)
    {
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(array('getTierPrice', '__wakeup'))
            ->getMock();
        $product->expects($this->once())->method('getTierPrice')->will($this->returnValue($tierPrices));
        $item = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            array(),
            array('getProduct', 'getProductType'),
            '',
            false
        );
        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $calledTimes = $tierPrices ? 'once' : 'never';
        $item->expects($this->{$calledTimes}())->method('getProductType')->will($this->returnValue($productType));
        return $item;
    }

    /**
     * @covers \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid::getItems
     */
    public function testGetItems()
    {
        $productId = 8;
        $itemQty = 23;
        $layoutMock = $this->getMock('Magento\Framework\View\LayoutInterface');
        $blockMock = $this->getMock('Magento\Framework\View\Element\AbstractBlock', ['getItems'], [], '', false);

        $itemMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            array('getProduct', 'setHasError', 'setQty', 'getQty', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getStockItem', 'getID', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );

        $checkMock = $this->getMock('Magento\Framework\Object', ['getMessage', 'getHasError'], [], '', false);

        $layoutMock->expects($this->once())->method('getParentName')->will($this->returnValue('parentBlock'));
        $layoutMock->expects($this->once())->method('getBlock')->with('parentBlock')
            ->will($this->returnValue($blockMock));

        $blockMock->expects($this->once())->method('getItems')->will($this->returnValue(array($itemMock)));

        $itemMock->expects($this->any())->method('getChildren')->will($this->returnValue(array($itemMock)));
        $itemMock->expects($this->any())->method('getProduct')->will($this->returnValue($productMock));
        $itemMock->expects($this->any())->method('getQty')->will($this->returnValue($itemQty));

        $productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $productMock->expects($this->any())->method('getStatus')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED));

        $checkMock->expects($this->any())->method('getMessage')->will($this->returnValue('Message'));
        $checkMock->expects($this->any())->method('getHasError')->will($this->returnValue(false));

        $this->stockItemService->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with(
                $this->equalTo($productId),
                $this->equalTo($itemQty),
                $this->equalTo($itemQty)
            )
            ->will($this->returnValue($checkMock));

        $this->block->getQuote()->setIsSuperMode(true);
        $items = $this->block->setLayout($layoutMock)->getItems();

        $this->assertEquals('Message', $items[0]->getMessage());
        $this->assertEquals(true, $this->block->getQuote()->getIsSuperMode());
    }

    protected function getGrid()
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid  $grid */
        $grid = $this->objectManager->getObject(
            'Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid',
            array(
                'context' => $this->objectManager->getObject(
                        'Magento\Backend\Block\Template\Context',
                        array('layout' => $this->layoutMock)
                    )
            )
        );

        return $grid;
    }

    public function testGetItemUnitPriceHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_unit_price')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));


        $this->assertEquals($html, $grid->getItemUnitPriceHtml($this->itemMock));
    }

    public function testGetItemRowTotalHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));


        $this->assertEquals($html, $grid->getItemRowTotalHtml($this->itemMock));
    }
    public function testGetItemRowTotalWithDiscountHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total_with_discount')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));


        $this->assertEquals($html, $grid->getItemRowTotalWithDiscountHtml($this->itemMock));
    }
}
