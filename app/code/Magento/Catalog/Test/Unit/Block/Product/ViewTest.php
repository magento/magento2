<?php declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Checkout\Helper\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface as UrlEncoderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test class for Product View Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @covers \Magento\Catalog\Block\Product\View
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    private View $view;

    /**
     * @var ConfigInterface|MockObject
     */
    private $productTypeConfig;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var Cart|MockObject
     */
    private $cartHelperMock;

    /**
     * @var Manager|MockObject
     */
    private $eventManagerMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private $jsonEncoderMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var StockRegistry|MockObject
     */
    private $stockRegistryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->productTypeConfig = $this->createMock(ConfigInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->cartHelperMock = $this->createMock(Cart::class);
        $this->eventManagerMock = $this->createMock(Manager::class);
        $this->jsonEncoderMock = $this->createMock(EncoderInterface::class);
        $this->productMock = $this->createMock(Product::class);
        $this->stockRegistryMock = $this->createMock(StockRegistry::class);
        $urlEncoderMock = $this->createMock(UrlEncoderInterface::class);
        $stringUtilsMock = $this->createMock(StringUtils::class);
        $productHelperMock = $this->createMock(ProductHelper::class);
        $localeFormatMock = $this->createMock(FormatInterface::class);
        $productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $this->contextMock->method('getCartHelper')->willReturn($this->cartHelperMock);
        $this->contextMock->method('getRegistry')->willReturn($this->registryMock);
        $this->contextMock->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->method('getStockRegistry')->willReturn($this->stockRegistryMock);

        $this->view = $helper->getObject(
            View::class,
            [
                'context' => $this->contextMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'urlEncoder' => $urlEncoderMock,
                'string' => $stringUtilsMock,
                'productHelper' => $productHelperMock,
                'productTypeConfig' => $this->productTypeConfig,
                'localeFormat' => $localeFormatMock,
                'customerSession' => $this->customerSessionMock,
                'productRepository' => $productRepositoryMock,
                'priceCurrency' => $priceCurrencyMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testShouldRenderQuantity(): void
    {
        $productMock = $this->createMock(Product::class);
        $this->registryMock->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'product'
        )->willReturn(
            $productMock
        );
        $productMock->expects($this->once())->method('getTypeId')->willReturn('id');
        $this->productTypeConfig->expects(
            $this->once()
        )->method(
            'isProductSet'
        )->with(
            'id'
        )->willReturn(
            true
        );
        $this->assertFalse($this->view->shouldRenderQuantity());
    }

    /**
     * @return void
     */
    public function testGetIdentities(): void
    {
        $productTags = ['cat_p_1'];
        $product = $this->createMock(Product::class);

        $product->expects($this->once())
            ->method('getIdentities')
            ->willReturn($productTags);
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->willReturnMap(
                [
                    ['product', $product],
                ]
            );
        $this->assertSame($productTags, $this->view->getIdentities());
    }

    /**
     * Unit test for getCustomerId()
     *
     * @return void
     */
    public function testGetCustomerId(): void
    {
        $expectedCustomerId = 123;

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($expectedCustomerId);

        $reflection = new \ReflectionClass($this->view);
        $method = $reflection->getMethod('getCustomerId');

        $this->assertSame($expectedCustomerId, $method->invoke($this->view));
    }

    /**
     * Unit test for getAddToCartUrl()
     *
     * @return void
     */
    public function testGetAddToCartUrl(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('wishlist_next')
            ->willReturn(true);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn('dummy_url');
        $this->cartHelperMock->expects($this->once())
            ->method('getAddUrl')
            ->willReturn('final_url');

        $result = $this->view->getAddToCartUrl($this->productMock);
        $this->assertSame('final_url', $result);
    }

    /**
     * Unit test for getWishlistOptions()
     *
     * @return void
     */
    public function testGetWishlistOptions(): void
    {
        $expectedTypeId = 'simple';
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($expectedTypeId);
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);
        $result = $this->view->getWishlistOptions();
        $this->assertSame(['productType' => $expectedTypeId], $result);
    }

    /**
     * Unit test for getJsonConfig()
     *
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $encodedJson = "[{'key':'value'}]";
        $priceAmountMock = $this->createMock(AmountInterface::class);
        $tierPriceList = [
            [
                'price_qty' => 2,
                'price' => $priceAmountMock
            ],
            [
                'price_qty' => 10,
                'price' => $priceAmountMock
            ]
        ];
        $priceInfoBaseMock = $this->createMock(Base::class);
        $tierPriceMock = $this->createMock(TierPrice::class);
        $regularPriceMock = $this->createMock(RegularPrice::class);
        $finalPriceMock = $this->createMock(FinalPrice::class);
        $typeMock = $this->createMock(AbstractType::class);

        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoBaseMock);
        $priceInfoBaseMock->method('getPrice')
            ->willReturnMap([
                ['tier_price', $tierPriceMock],
                ['regular_price', $regularPriceMock],
                ['final_price', $finalPriceMock]
            ]);
        $tierPriceMock->expects($this->once())
            ->method('getTierPriceList')
            ->willReturn($tierPriceList);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeMock);
        $typeMock->expects($this->once())
            ->method('hasOptions')
            ->willReturn(true);
        $regularPriceMock->expects($this->exactly(2))
            ->method('getAmount')
            ->willReturn($priceAmountMock);
        $finalPriceMock->expects($this->exactly(2))
            ->method('getAmount')
            ->willReturn($priceAmountMock);
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->willReturn($encodedJson);

        $this->assertSame($encodedJson, $this->view->getJsonConfig());
    }

    /**
     * Unit test for getJsonConfig() without options
     *
     * @return void
     */
    public function testGetJsonConfigWithNoOptions(): void
    {
        $encodedJson = "[{'key':'value'}]";
        $priceAmountMock = $this->createMock(AmountInterface::class);
        $tierPriceList = [
            [
                'price_qty' => 2,
                'price' => $priceAmountMock
            ],
            [
                'price_qty' => 10,
                'price' => $priceAmountMock
            ]
        ];
        $priceInfoBaseMock = $this->createMock(Base::class);
        $tierPriceMock = $this->createMock(TierPrice::class);
        $typeMock = $this->createMock(AbstractType::class);

        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoBaseMock);
        $priceInfoBaseMock->method('getPrice')
            ->willReturn($tierPriceMock);
        $tierPriceMock->expects($this->once())
            ->method('getTierPriceList')
            ->willReturn($tierPriceList);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeMock);
        $typeMock->expects($this->once())
            ->method('hasOptions')
            ->willReturn(false);
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->willReturn($encodedJson);

        $this->assertSame($encodedJson, $this->view->getJsonConfig());
    }

    /**
     * Unit test for hasRequiredOptions()
     *
     * @return void
     */
    public function testHasRequiredOptions(): void
    {
        $typeMock = $this->createMock(AbstractType::class);

        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeMock);
        $typeMock->expects($this->once())
            ->method('hasRequiredOptions')
            ->willReturn(true);

        $this->assertTrue($this->view->hasRequiredOptions());
    }

    /**
     * Unit test for isStartCustomization()
     *
     * @return void
     */
    public function testIsStartCustomizationReturnsFalse(): void
    {
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $this->assertFalse($this->view->isStartCustomization());
    }

    /**
     * Unit test for isStartCustomization() with configure mode set to true
     *
     * @return void
     */
    public function testIsStartCustomizationReturnsTrueWithConfigureMode(): void
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getConfigureMode'])
            ->getMock();

        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $productMock->method('getConfigureMode')
            ->willReturn(true);

        $this->assertTrue($this->view->isStartCustomization());
    }

    /**
     * Unit test for getProductDefaultQty() with data provider
     *
     * @dataProvider productQtyDataProvider
     * @param int $minQty
     * @param int $configuredQty
     * @param int $expectedQty
     * @return void
     */
    public function testGetProductDefaultQty(int $minQty, int $configuredQty, int $expectedQty): void
    {
        $storeMock = $this->createMock(Store::class);
        $stockItemMock = $this->createMock(Item::class);
        $configMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQty'])
            ->getMock();

        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);
        $this->productMock->method('getStore')
            ->willReturn($storeMock);
        $this->stockRegistryMock->method('getStockItem')
            ->willReturn($stockItemMock);
        $this->productMock->method('getPreconfiguredValues')
            ->willReturn($configMock);
        $stockItemMock->method('getMinSaleQty')
            ->willReturn($minQty);
        $configMock->method('getQty')
            ->willReturn($configuredQty);

        $this->assertSame($expectedQty, $this->view->getProductDefaultQty());
    }

    /**
     * Data provider for testGetProductDefaultQty()
     *
     * @return array
     */
    public static function productQtyDataProvider(): array
    {
        return [
            [5, 10, 10],
            [5, 2, 5],
        ];
    }

    /**
     * Unit test for getOptionsContainer()
     *
     * @return void
     */
    public function testGetOptionsContainerReturnsContainer1(): void
    {
        $expectedOptionsContainer = 'container1';
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOptionsContainer'])
            ->getMock();
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $productMock->method('getOptionsContainer')
            ->willReturn($expectedOptionsContainer);

        $this->assertSame($expectedOptionsContainer, $this->view->getOptionsContainer());
    }

    /**
     * Unit test for getOptionsContainer()
     *
     * @return void
     */
    public function testGetOptionsContainerReturnsContainer2(): void
    {
        $expectedOptionsContainer = 'container2';
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $this->assertSame($expectedOptionsContainer, $this->view->getOptionsContainer());
    }

    /**
     * Unit test for testGetQuantityValidators()
     *
     * @return void
     */
    public function testGetQuantityValidators(): void
    {
        $result = $this->view->getQuantityValidators();
        $this->assertIsArray($result);
        $this->assertSame(["required-number" => true], $result);
    }

    /**
     * Unit test for canEmailToFriend()
     *
     * @return void
     */
    public function testCanEmailToFriend(): void
    {
        $this->assertFalse($this->view->canEmailToFriend());
    }

    /**
     * Unit test for getProduct()
     *
     * @return void
     */
    public function testGetProductReturnsProductFromRegistry(): void
    {
        $productMock = $this->createMock(Product::class);
        $this->registryMock->expects($this->exactly(2))
            ->method('registry')
            ->willReturn($productMock);

        $this->assertSame($productMock, $this->view->getProduct());
    }

    /**
     * Unit test for hasOptions() using data provider
     *
     * @dataProvider hasOptionsDataProvider
     *
     * @param bool $hasOptions
     * @return void
     */
    public function testHasOptionsWithProvider(bool $hasOptions): void
    {
        $typeMock = $this->createMock(AbstractType::class);

        $this->registryMock->expects($this->exactly(4))
            ->method('registry')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeMock);
        $typeMock->expects($this->once())
            ->method('hasOptions')
            ->with($this->productMock)
            ->willReturn($hasOptions);

        $this->assertSame($hasOptions, $this->view->hasOptions());
    }

    /**
     * Data provider for testHasOptionsWithProvider()
     *
     * @return array
     */
    public static function hasOptionsDataProvider(): array
    {
        return [
            'has_options' => [true],
            'no_options' => [false],
        ];
    }
}
