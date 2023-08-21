<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\StockState;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart\RequestInfoFilterInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends TestCase
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var StockItemInterface|MockObject
     */
    protected $stockItemMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    /**
     * @var MockObject
     */
    protected $stockState;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var MockObject
     */
    private $storeMock;

    /**
     * @var MockObject
     */
    private $productRepository;

    /**
     * @var MockObject
     */
    private $requestInfoFilterMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();
        $this->stockItemMock = $this->createPartialMock(
            Item::class,
            ['getMinSaleQty']
        );
        $this->stockState = $this->createPartialMock(
            StockState::class,
            ['suggestQty']
        );
        $this->storeMock =
            $this->createPartialMock(Store::class, ['getWebsiteId', 'getId']);
        $this->requestInfoFilterMock = $this->createMock(
            RequestInfoFilterInterface::class
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
            Cart::class,
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

    /**
     * @return void
     */
    public function testSuggestItemsQty(): void
    {
        $data = [[] , ['qty' => -2], ['qty' => 3], ['qty' => 3.5], ['qty' => 5], ['qty' => 4]];
        $this->quoteMock->expects($this->any())
            ->method('getItemById')
            ->willReturnMap([
                [2, $this->prepareQuoteItemMock(2)],
                [3, $this->prepareQuoteItemMock(3)],
                [4, $this->prepareQuoteItemMock(4)],
                [5, $this->prepareQuoteItemMock(5)]
            ]);

        $this->stockState
            ->method('suggestQty')
            ->willReturnOnConsecutiveCalls(3.0, 3.5);

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
                ['qty' => 4]
            ],
            $this->cart->suggestItemsQty($data)
        );
    }

    /**
     * @return void
     */
    public function testUpdateItems(): void
    {
        $data = [['qty' => 5.5, 'before_suggest_qty' => 5.5]];
        $infoDataObject = $this->objectManagerHelper->getObject(
            DataObject::class,
            ['data' => $data]
        );

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->eventManagerMock
            ->method('dispatch')
            ->withConsecutive(
                [
                    'checkout_cart_update_items_before',
                    ['cart' => $this->cart, 'info' => $infoDataObject]
                ],
                [
                    'checkout_cart_update_items_after',
                    ['cart' => $this->cart, 'info' => $infoDataObject]
                ]
            );

        $result = $this->cart->updateItems($data);
        $this->assertSame($this->cart, $result);
    }

    /**
     * @param int|bool $itemId
     *
     * @return MockObject|bool
     */
    public function prepareQuoteItemMock($itemId)
    {
        $store = $this->createPartialMock(Store::class, ['getId', 'getWebsiteId']);
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
                    Product::class,
                    ['getStore', 'getId']
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
                    Product::class,
                    ['getStore', 'getId']
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
     *
     * @return void
     * @dataProvider useQtyDataProvider
     */
    public function testGetSummaryQty(bool $useQty): void
    {
        $quoteId = 1;
        $itemsCount = 1;
        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['getItemsCount', 'getItemsQty']
        );

        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->checkoutSessionMock
            ->method('getQuoteId')
            ->willReturn($quoteId);
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->willReturn(true);

        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with('checkout/cart_link/use_qty', ScopeInterface::SCOPE_STORE)
            ->willReturn($useQty);

        $qtyMethodName = ($useQty) ? 'getItemsQty' : 'getItemsCount';
        $quoteMock->expects($this->once())->method($qtyMethodName)->willReturn($itemsCount);

        $this->assertEquals($itemsCount, $this->cart->getSummaryQty());
    }

    /**
     * @return array
     */
    public function useQtyDataProvider(): array
    {
        return [
            ['useQty' => true],
            ['useQty' => false]
        ];
    }

    /**
     * Test successful scenarios for AddProduct.
     *
     * @param int|Product $productInfo
     * @param DataObject|int|array $requestInfo
     *
     * @return void
     * @dataProvider addProductDataProvider
     */
    public function testAddProduct($productInfo, $requestInfo): void
    {
        $product = $this->createPartialMock(
            Product::class,
            ['getStore', 'getWebsiteIds', 'getProductUrl', 'getId']
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

        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->willReturn(1);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->eventManagerMock
            ->method('dispatch')
            ->withConsecutive(
                [
                    'checkout_cart_product_add_before',
                    ['info' => $requestInfo, 'product' => $product]
                ],
                [
                    'checkout_cart_product_add_after',
                    ['quote_item' => 1, 'product' => $product]
                ]
            );

        if (!$productInfo) {
            $productInfo = $product;
        }
        $result = $this->cart->addProduct($productInfo, $requestInfo);
        $this->assertSame($this->cart, $result);
    }

    /**
     * Test exception on adding product for AddProduct.
     *
     * @return void
     */
    public function testAddProductException(): void
    {
        $product = $this->createPartialMock(
            Product::class,
            ['getStore', 'getWebsiteIds', 'getProductUrl', 'getId']
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

        $this->expectException(LocalizedException::class);
        $this->cart->addProduct(4, 4);
    }

    /**
     * Test bad parameters on adding product for AddProduct.
     *
     * @return void
     */
    public function testAddProductExceptionBadParams(): void
    {
        $product = $this->createPartialMock(
            Product::class,
            ['getWebsiteIds', 'getId']
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
        $this->expectException(LocalizedException::class);
        $this->cart->addProduct(4, 'bad');
    }

    /**
     * Data provider for testAddProduct.
     *
     * @return array
     */
    public function addProductDataProvider(): array
    {
        $obj = new ObjectManagerHelper($this);
        $data = ['qty' => 5.5, 'sku' => 'prod'];

        return [
            'prod_int_info_int' => [4, 4],
            'prod_int_info_array' => [ 4, $data],
            'prod_int_info_object' => [
                4,
                $obj->getObject(
                    DataObject::class,
                    ['data' => $data]
                )
            ],
            'prod_obj_info_int' => [null, 4],
            'prod_obj_info_array' => [ null, $data],
            'prod_obj_info_object' => [
                null,
                $obj->getObject(
                    DataObject::class,
                    ['data' => $data]
                )
            ]
        ];
    }
}
