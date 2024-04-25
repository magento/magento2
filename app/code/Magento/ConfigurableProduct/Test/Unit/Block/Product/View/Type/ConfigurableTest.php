<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Product\View\Type;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Pricing\Price\TierPriceInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\State;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Tax\Helper\Data as TaxData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var ArrayUtils|MockObject
     */
    private $arrayUtils;

    /**
     * @var EncoderInterface|MockObject
     */
    private $jsonEncoder;

    /**
     * @var Data|MockObject
     */
    private $helper;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var CurrentCustomer|MockObject
     */
    private $currentCustomer;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var Currency|MockObject
     */
    private $currency;

    /**
     * @var ConfigurableAttributeData|MockObject
     */
    private $configurableAttributeData;

    /**
     * @var Format|MockObject
     */
    private $localeFormat;

    /**
     * @var Configurable|MockObject
     */
    private $block;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var MockObject
     */
    private $customerSession;

    /**
     * @var MockObject
     */
    private $variationPricesMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->mockContextObject();

        $this->arrayUtils = $this->getMockBuilder(ArrayUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->getMockForAbstractClass();

        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currentCustomer = $this->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getAppState')
            ->willReturn($appState);
        $appState->expects($this->any())
            ->method('getAreaCode')
            ->willReturn('frontend');
        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($urlBuilder);
        $fileResolverMock = $this
            ->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResolver')
            ->willReturn($fileResolverMock);
        $taxData = $this->createMock(TaxData::class);
        $this->context->expects($this->once())
            ->method('getTaxData')
            ->willReturn($taxData);
        $this->currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableAttributeData = $this->getMockBuilder(
            ConfigurableAttributeData::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeFormat = $this->getMockBuilder(Format::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->variationPricesMock = $this->createMock(
            Prices::class
        );

        $this->block = new Configurable(
            $this->context,
            $this->arrayUtils,
            $this->jsonEncoder,
            $this->helper,
            $this->product,
            $this->currentCustomer,
            $this->priceCurrency,
            $this->configurableAttributeData,
            [],
            $this->localeFormat,
            $this->customerSession,
            $this->variationPricesMock
        );
    }

    /**
     * Provide cache key info
     *
     * @return array
     */
    public static function cacheKeyProvider(): array
    {
        return [
            'without_currency_and_customer_group' => [
                [
                    0 => 'BLOCK_TPL',
                    1 => 'default',
                    2 => null,
                    'base_url' => null,
                    'template' => null,
                    3 => null,
                    4 => null,
                ],
                null,
                null,
            ],
            'with_customer_group' => [
                [
                    0 => 'BLOCK_TPL',
                    1 => 'default',
                    2 => null,
                    'base_url' => null,
                    'template' => null,
                    3 => null,
                    4 => 1,
                ],
                null,
                1,
            ],
            'with_price_currency' => [
                [
                    0 => 'BLOCK_TPL',
                    1 => 'default',
                    2 => null,
                    'base_url' => null,
                    'template' => null,
                    3 => 'USD',
                    4 => null,
                ],
                'USD',
                null,
            ]
        ];
    }

    /**
     * Test cache Tags
     * @dataProvider cacheKeyProvider
     * @param array $expected
     * @param string|null $priceCurrency
     * @param int|null $customerGroupId
     */
    public function testGetCacheKeyInfo(
        array $expected,
        ?string $priceCurrency = null,
        ?int $customerGroupId = null
    ): void {
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getCurrentCurrency'])
            ->getMockForAbstractClass();
        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn('default');

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->priceCurrency->expects($this->once())
            ->method('getCurrency')
            ->willReturn($this->currency);
        $this->currency->expects($this->once())
            ->method('getCode')
            ->willReturn($priceCurrency);
        $this->customerSession->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $actual = $this->block->getCacheKeyInfo();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Check that getJsonConfig() method returns expected value
     */
    public function testGetJsonConfig(): void
    {
        $productId = 1;
        $amount = 10.50;
        $priceQty = 1;
        $percentage = 10;

        $amountMock = $this->getAmountMock($amount);

        $priceMock = $this->getMockBuilder(PriceInterface::class)
            ->onlyMethods(['getAmount'])
            ->getMockForAbstractClass();
        $priceMock->expects($this->any())->method('getAmount')->willReturn($amountMock);
        $tierPriceMock = $this->getTierPriceMock($amountMock, $priceQty, $percentage);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeMock = $this->getProductTypeMock($productMock);

        $priceInfoMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->willReturnMap(
                [
                    ['regular_price', $priceMock],
                    ['final_price', $priceMock],
                    ['tier_price', $tierPriceMock],
                ]
            );

        $productMock->expects($this->any())->method('getTypeInstance')->willReturn($productTypeMock);
        $productMock->expects($this->any())->method('getPriceInfo')->willReturn($priceInfoMock);
        $productMock->expects($this->any())->method('isSaleable')->willReturn(true);
        $productMock->expects($this->any())->method('getId')->willReturn($productId);
        $productMock->expects($this->any())->method('getStatus')
            ->willReturn(Status::STATUS_ENABLED);

        $this->helper->expects($this->any())
            ->method('getOptions')
            ->with($productMock, [$productMock])
            ->willReturn([]);

        $attributesData = [
            'attributes' => [],
            'defaultValues' => [],
        ];

        $this->configurableAttributeData->expects($this->any())
            ->method('getAttributesData')
            ->with($productMock, [])
            ->willReturn($attributesData);

        $this->localeFormat->expects($this->atLeastOnce())->method('getPriceFormat')->willReturn([]);
        $this->localeFormat->expects($this->any())
            ->method('getNumber')
            ->willReturnArgument(0);

        $this->variationPricesMock->expects($this->once())
            ->method('getFormattedPrices')
            ->with($priceInfoMock)
            ->willReturn(
                [
                    'baseOldPrice' => [
                        'amount' => $amount,
                    ],
                    'oldPrice' => [
                        'amount' => $amount,
                    ],
                    'basePrice' => [
                        'amount' => $amount,
                    ],
                    'finalPrice' => [
                        'amount' => $amount,
                    ],
                ]
            );

        $expectedArray = $this->getExpectedArray($productId, $amount, $priceQty, $percentage);
        $expectedJson = json_encode($expectedArray);

        $this->jsonEncoder->expects($this->once())->method('encode')->with($expectedArray)->willReturn($expectedJson);

        $this->block->setData('product', $productMock);
        $result = $this->block->getJsonConfig();
        $this->assertEquals($expectedJson, $result);
    }

    /**
     * Retrieve array with expected parameters for method getJsonConfig()
     *
     * @param int $productId
     * @param double $amount
     * @param int $priceQty
     * @param int $percentage
     * @return array
     */
    private function getExpectedArray($productId, $amount, $priceQty, $percentage): array
    {
        $expectedArray = [
            'attributes' => [],
            'template' => '<%- data.price %>',
            'currencyFormat' => '%s',
            'optionPrices' => [
                $productId => [
                    'baseOldPrice' => [
                        'amount' => $amount,
                    ],
                    'oldPrice' => [
                        'amount' => $amount,
                    ],
                    'basePrice' => [
                        'amount' => $amount,
                    ],
                    'finalPrice' => [
                        'amount' => $amount,
                    ],
                    'tierPrices' => [
                        0 => [
                            'qty' => $priceQty,
                            'price' => $amount,
                            'percentage' => $percentage,
                        ],
                    ],
                    'msrpPrice' => [
                        'amount' => null,
                    ]
                ],
            ],
            'priceFormat' => [],
            'prices' => [
                'baseOldPrice' => [
                    'amount' => $amount,
                ],
                'oldPrice' => [
                    'amount' => $amount,
                ],
                'basePrice' => [
                    'amount' => $amount,
                ],
                'finalPrice' => [
                    'amount' => $amount,
                ],
            ],
            'productId' => $productId,
            'chooseText' => __('Choose an Option...'),
            'images' => [],
            'index' => [],
            'salable' => [],
            'canDisplayShowOutOfStockStatus' => false
        ];

        return $expectedArray;
    }

    /**
     * Retrieve mocks of \Magento\ConfigurableProduct\Model\Product\Type\Configurable object
     *
     * @param MockObject $productMock
     * @return MockObject
     */
    private function getProductTypeMock(MockObject $productMock): MockObject
    {
        $currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects($this->any())
            ->method('getOutputFormat')
            ->willReturn('%s');

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['getCurrentCurrency'])
            ->getMockForAbstractClass();
        $storeMock->expects($this->any())
            ->method('getCurrentCurrency')
            ->willReturn($currencyMock);

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $productTypeMock = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock->expects($this->any())
            ->method('getStoreFilter')
            ->with($productMock)
            ->willReturn($storeMock);
        $productTypeMock->expects($this->any())
            ->method('getUsedProducts')
            ->with($productMock)
            ->willReturn([$productMock]);

        return $productTypeMock;
    }

    /**
     * Create mocks for \Magento\Catalog\Block\Product\Context object
     *
     * @return void
     */
    protected function mockContextObject()
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->storeManager);
    }

    /**
     * Retrieve mock of \Magento\Framework\Pricing\Amount\AmountInterface object
     *
     * @param float $amount
     * @return MockObject
     */
    protected function getAmountMock($amount): MockObject
    {
        $amountMock = $this->getMockBuilder(AmountInterface::class)
            ->onlyMethods(['getValue', 'getBaseAmount'])
            ->getMockForAbstractClass();
        $amountMock->expects($this->any())
            ->method('getValue')
            ->willReturn($amount);
        $amountMock->expects($this->any())
            ->method('getBaseAmount')
            ->willReturn($amount);

        return $amountMock;
    }

    /**
     * Retrieve mock of \Magento\Catalog\Pricing\Price\TierPriceInterface object
     *
     * @param MockObject $amountMock
     * @param float $priceQty
     * @param int $percentage
     * @return MockObject
     */
    protected function getTierPriceMock(MockObject $amountMock, $priceQty, $percentage)
    {
        $tierPrice = [
            'price_qty' => $priceQty,
            'price' => $amountMock,
        ];

        $tierPriceMock = $this->getMockBuilder(TierPriceInterface::class)
            ->addMethods(['getSavePercent'])
            ->onlyMethods(['getTierPriceList'])
            ->getMockForAbstractClass();
        $tierPriceMock->expects($this->any())
            ->method('getTierPriceList')
            ->willReturn([$tierPrice]);
        $tierPriceMock->expects($this->any())
            ->method('getSavePercent')
            ->willReturn($percentage);

        return $tierPriceMock;
    }
}
