<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CartTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Model\Cart */
    protected $cart;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestInfoFilterMock;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $this->productRepository = $this->getMockForAbstractClass(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();
        $this->stockItemMock = $this->getMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getMinSaleQty', '__wakeup'],
            [],
            '',
            false
        );
        $this->stockState = $this->getMock(
            \Magento\CatalogInventory\Model\StockState::class,
            ['suggestQty', '__wakeup'],
            [],
            '',
            false
        );
        $this->storeMock =
            $this->getMock(\Magento\Store\Model\Store::class, ['getWebsiteId', 'getId', '__wakeup'], [], '', false);
        $this->requestInfoFilterMock =
            $this->getMockForAbstractClass(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class);

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(10));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cart = $this->objectManagerHelper->getObject(
            \Magento\Checkout\Model\Cart::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'stockRegistry' => $this->stockRegistry,
                'stockState' => $this->stockState,
                'customerSession' => $this->customerSessionMock,
                'eventManager' => $this->eventManagerMock,
                'storeManager' => $this->storeManagerMock,
                'productRepository' => $this->productRepository
            ]
        );

        $this->objectManagerHelper
            ->setBackwardCompatibleProperty($this->cart, 'requestInfoFilter', $this->requestInfoFilterMock);
    }

    public function testSuggestItemsQty()
    {
        $data = [[] , ['qty' => -2], ['qty' => 3], ['qty' => 3.5], ['qty' => 5], ['qty' => 4]];
        $this->quoteMock->expects($this->any())
            ->method('getItemById')
            ->will($this->returnValueMap([
                [2, $this->prepareQuoteItemMock(2)],
                [3, $this->prepareQuoteItemMock(3)],
                [4, $this->prepareQuoteItemMock(4)],
                [5, $this->prepareQuoteItemMock(5)],
            ]));

        $this->stockState->expects($this->at(0))
            ->method('suggestQty')
            ->will($this->returnValue(3.0));
        $this->stockState->expects($this->at(1))
            ->method('suggestQty')
            ->will($this->returnValue(3.5));

        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->assertSame(
            [
                [],
                ['qty' => -2],
                ['qty' => 3., 'before_suggest_qty' => 3.],
                ['qty' => 3.5, 'before_suggest_qty' => 3.5],
                ['qty' => 5],
                ['qty' => 4],
            ],
            $this->cart->suggestItemsQty($data)
        );
    }

    public function testUpdateItems()
    {
        $data = [['qty' => 5.5, 'before_suggest_qty' => 5.5]];
        $infoDataObject = $this->objectManagerHelper->getObject(
            \Magento\Framework\DataObject::class,
            ['data' => $data]
        );

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->eventManagerMock->expects($this->at(0))->method('dispatch')->with(
            'checkout_cart_update_items_before',
            ['cart' => $this->cart, 'info' => $infoDataObject]
        );
        $this->eventManagerMock->expects($this->at(1))->method('dispatch')->with(
            'checkout_cart_update_items_after',
            ['cart' => $this->cart, 'info' => $infoDataObject]
        );

        $result = $this->cart->updateItems($data);
        $this->assertSame($this->cart, $result);
    }

    /**
     * @param int|bool $itemId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function prepareQuoteItemMock($itemId)
    {
        $store = $this->getMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup'], [], '', false);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $store->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(10));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        switch ($itemId) {
            case 2:
                $product = $this->getMock(
                    \Magento\Catalog\Model\Product::class,
                    ['getStore', 'getId', '__wakeup'],
                    [],
                    '',
                    false
                );
                $product->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue(4));
                $product->expects($this->once())
                    ->method('getStore')
                    ->will($this->returnValue($store));
                break;
            case 3:
                $product = $this->getMock(
                    \Magento\Catalog\Model\Product::class,
                    ['getStore', 'getId', '__wakeup'],
                    [],
                    '',
                    false
                );
                $product->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue(5));
                $product->expects($this->once())
                    ->method('getStore')
                    ->will($this->returnValue($store));
                break;
            case 4:
                $product = false;
                break;
            default:
                return false;
        }

        $quoteItem = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $quoteItem->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));
        return $quoteItem;
    }

    /**
     * @param boolean $useQty
     * @dataProvider useQtyDataProvider
     */
    public function testGetSummaryQty($useQty)
    {
        $quoteId = 1;
        $itemsCount = 1;
        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getItemsCount', 'getItemsQty', '__wakeup'],
            [],
            '',
            false
        );

        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->checkoutSessionMock->expects($this->at(2))->method('getQuoteId')->will($this->returnValue($quoteId));
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->will($this->returnValue(true));

        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with('checkout/cart_link/use_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($useQty));

        $qtyMethodName = ($useQty) ? 'getItemsQty' : 'getItemsCount';
        $quoteMock->expects($this->once())->method($qtyMethodName)->will($this->returnValue($itemsCount));

        $this->assertEquals($itemsCount, $this->cart->getSummaryQty());
    }

    public function useQtyDataProvider()
    {
        return [
            ['useQty' => true],
            ['useQty' => false]
        ];
    }

    /**
     * Test successful scenarios for AddProduct
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @dataProvider addProductDataProvider
     */
    public function testAddProduct($productInfo, $requestInfo)
    {
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getStore', 'getWebsiteIds', 'getProductUrl', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(4));
        $product->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue([10]));
        $product->expects($this->any())
            ->method('getProductUrl')
            ->will($this->returnValue('url'));
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($product));
        $this->quoteMock->expects($this->once())
        ->method('addProduct')
        ->will($this->returnValue(1));
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->eventManagerMock->expects($this->at(0))->method('dispatch')->with(
            'checkout_cart_product_add_after',
            ['quote_item' => 1, 'product' => $product]
        );

        if (!$productInfo) {
            $productInfo = $product;
        }
        $result = $this->cart->addProduct($productInfo, $requestInfo);
        $this->assertSame($this->cart, $result);
    }

    /**
     * Test exception on adding product for AddProduct
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAddProductException()
    {
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getStore', 'getWebsiteIds', 'getProductUrl', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(4));
        $product->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue([10]));
        $product->expects($this->any())
            ->method('getProductUrl')
            ->will($this->returnValue('url'));
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($product));
        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->will($this->returnValue('error'));
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->eventManagerMock->expects($this->never())->method('dispatch')->with(
            'checkout_cart_product_add_after',
            ['quote_item' => 1, 'product' => $product]
        );
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class);
        $this->cart->addProduct(4, 4);
    }

    /**
     * Test bad parameters on adding product for AddProduct
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAddProductExceptionBadParams()
    {
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getWebsiteIds', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(4));
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue([10]));
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($product));

        $this->eventManagerMock->expects($this->never())->method('dispatch')->with(
            'checkout_cart_product_add_after',
            ['quote_item' => 1, 'product' => $product]
        );
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class);
        $this->cart->addProduct(4, 'bad');
    }

    /**
     * Data provider for testAddProduct
     *
     * @return array
     */
    public function addProductDataProvider()
    {
        $obj = new ObjectManagerHelper($this) ;
        $data = ['qty' => 5.5, 'sku' => 'prod'];

        return [
            'prod_int_info_int' => [4, 4],
            'prod_int_info_array' => [ 4, $data],
            'prod_int_info_object' => [
                4,
                $obj->getObject(
                    \Magento\Framework\DataObject::class,
                    ['data' => $data]
                )
            ],
            'prod_obj_info_int' => [null, 4],
            'prod_obj_info_array' => [ null, $data],
            'prod_obj_info_object' => [
                null,
                $obj->getObject(
                    \Magento\Framework\DataObject::class,
                    ['data' => $data]
                )
            ]
        ];
    }
}
