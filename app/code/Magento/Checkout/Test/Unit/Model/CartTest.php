<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CartTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Model\Cart */
    protected $cart;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockState;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $productRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $requestInfoFilterMock;

    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->productRepository = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();
        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getMinSaleQty', '__wakeup']
        );
        $this->stockState = $this->createPartialMock(
            \Magento\CatalogInventory\Model\StockState::class,
            ['suggestQty', '__wakeup']
        );
        $this->storeMock =
            $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId', 'getId', '__wakeup']);
        $this->requestInfoFilterMock = $this->createMock(
            \Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(10);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(10);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

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
            ->willReturnMap([
                [2, $this->prepareQuoteItemMock(2)],
                [3, $this->prepareQuoteItemMock(3)],
                [4, $this->prepareQuoteItemMock(4)],
                [5, $this->prepareQuoteItemMock(5)],
            ]);

        $this->stockState->expects($this->at(0))
            ->method('suggestQty')
            ->willReturn(3.0);
        $this->stockState->expects($this->at(1))
            ->method('suggestQty')
            ->willReturn(3.5);

        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

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
            ->willReturn($this->quoteMock);
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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function prepareQuoteItemMock($itemId)
    {
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup', 'getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(10);
        $store->expects($this->any())
            ->method('getId')
            ->willReturn(10);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        switch ($itemId) {
            case 2:
                $product = $this->createPartialMock(
                    \Magento\Catalog\Model\Product::class,
                    ['getStore', 'getId', '__wakeup']
                );
                $product->expects($this->once())
                    ->method('getId')
                    ->willReturn(4);
                $product->expects($this->once())
                    ->method('getStore')
                    ->willReturn($store);
                break;
            case 3:
                $product = $this->createPartialMock(
                    \Magento\Catalog\Model\Product::class,
                    ['getStore', 'getId', '__wakeup']
                );
                $product->expects($this->once())
                    ->method('getId')
                    ->willReturn(5);
                $product->expects($this->once())
                    ->method('getStore')
                    ->willReturn($store);
                break;
            case 4:
                $product = false;
                break;
            default:
                return false;
        }

        $quoteItem = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $quoteItem->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
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
        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getItemsCount', 'getItemsQty', '__wakeup']
        );

        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->checkoutSessionMock->expects($this->at(2))->method('getQuoteId')->willReturn($quoteId);
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->willReturn(true);

        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with('checkout/cart_link/use_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($useQty);

        $qtyMethodName = ($useQty) ? 'getItemsQty' : 'getItemsCount';
        $quoteMock->expects($this->once())->method($qtyMethodName)->willReturn($itemsCount);

        $this->assertEquals($itemsCount, $this->cart->getSummaryQty());
    }

    /**
     * @return array
     */
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
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getStore', 'getWebsiteIds', 'getProductUrl', 'getId', '__wakeup']
        );
        $product->expects($this->any())
            ->method('getId')
            ->willReturn(4);
        $product->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn([10]);
        $product->expects($this->any())
            ->method('getProductUrl')
            ->willReturn('url');
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->willReturn($product);

        $this->eventManagerMock->expects($this->at(0))->method('dispatch')->with(
            'checkout_cart_product_add_before',
            ['info' => $requestInfo, 'product' => $product]
        );

        $this->quoteMock->expects($this->once())
        ->method('addProduct')
        ->willReturn(1);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->eventManagerMock->expects($this->at(1))->method('dispatch')->with(
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
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getStore', 'getWebsiteIds', 'getProductUrl', 'getId', '__wakeup']
        );
        $product->expects($this->any())
            ->method('getId')
            ->willReturn(4);
        $product->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn([10]);
        $product->expects($this->any())
            ->method('getProductUrl')
            ->willReturn('url');
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->willReturn($product);

        $this->eventManagerMock->expects($this->once())->method('dispatch')->with(
            'checkout_cart_product_add_before',
            ['info' => 4, 'product' => $product]
        );

        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->willReturn('error');
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->cart->addProduct(4, 4);
    }

    /**
     * Test bad parameters on adding product for AddProduct
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAddProductExceptionBadParams()
    {
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getWebsiteIds', 'getId', '__wakeup']
        );
        $product->expects($this->any())
            ->method('getId')
            ->willReturn(4);
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn([10]);
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->willReturn($product);

        $this->eventManagerMock->expects($this->never())->method('dispatch')->with(
            'checkout_cart_product_add_before',
            ['info' => 'bad', 'product' => $product]
        );

        $this->eventManagerMock->expects($this->never())->method('dispatch')->with(
            'checkout_cart_product_add_after',
            ['quote_item' => 1, 'product' => $product]
        );
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->cart->addProduct(4, 'bad');
    }

    /**
     * Data provider for testAddProduct
     *
     * @return array
     */
    public function addProductDataProvider()
    {
        $obj = new ObjectManagerHelper($this);
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
