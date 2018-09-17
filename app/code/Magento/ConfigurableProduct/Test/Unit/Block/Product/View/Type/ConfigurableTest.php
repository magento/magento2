<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Block\Product\View\Type;

use Magento\Customer\Model\Session;
use Magento\Framework\App\State;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $arrayUtils;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonEncoder;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currentCustomer;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currency;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurableAttributeData;

    /**
     * @var \Magento\Framework\Locale\Format|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeFormat;

    /**
     * @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $block;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $variationPricesMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->mockContextObject();

        $this->arrayUtils = $this->getMockBuilder(\Magento\Framework\Stdlib\ArrayUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoder = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->getMockForAbstractClass();

        $this->helper = $this->getMockBuilder(\Magento\ConfigurableProduct\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Helper\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currentCustomer = $this->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getAppState')
            ->willReturn($appState);
        $appState->expects($this->any())
            ->method('getAreaCode')
            ->willReturn('frontend');
        $urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($urlBuilder);
        $fileResolverMock = $this
            ->getMockBuilder(\Magento\Framework\View\Element\Template\File\Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResolver')
            ->willReturn($fileResolverMock);
        $this->currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableAttributeData = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ConfigurableAttributeData::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeFormat = $this->getMockBuilder(\Magento\Framework\Locale\Format::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->variationPricesMock = $this->createMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices::class
        );

        $this->block = new \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable(
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
    public function cacheKeyProvider(): array
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
     * @param string|null $customerGroupId
     */
    public function testGetCacheKeyInfo(array $expected, string $priceCurrency = null, string $customerGroupId = null)
    {
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods([
                'getCurrentCurrency',
            ])
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
    public function testGetJsonConfig()
    {
        $productId = 1;
        $amount = 10.50;
        $priceQty = 1;
        $percentage = 10;

        $amountMock = $this->getAmountMock($amount);

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->setMethods([
                'getAmount',
            ])
            ->getMockForAbstractClass();
        $priceMock->expects($this->any())->method('getAmount')->willReturn($amountMock);
        $tierPriceMock = $this->getTierPriceMock($amountMock, $priceQty, $percentage);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeMock = $this->getProductTypeMock($productMock);

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->willReturnMap([
                ['regular_price', $priceMock],
                ['final_price', $priceMock],
                ['tier_price', $tierPriceMock],
            ]);

        $productMock->expects($this->any())->method('getTypeInstance')->willReturn($productTypeMock);
        $productMock->expects($this->any())->method('getPriceInfo')->willReturn($priceInfoMock);
        $productMock->expects($this->any())->method('isSaleable')->willReturn(true);
        $productMock->expects($this->any())->method('getId')->willReturn($productId);

        $this->helper->expects($this->any())
            ->method('getOptions')
            ->with($productMock, [$productMock])
            ->willReturn([]);
        $this->product->expects($this->any())->method('getSkipSaleableCheck')->willReturn(true);

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
                ],
            ],
            'priceFormat' => [],
            'prices' => [
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
        ];

        return $expectedArray;
    }

    /**
     * Retrieve mocks of \Magento\ConfigurableProduct\Model\Product\Type\Configurable object
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $productMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductTypeMock(\PHPUnit_Framework_MockObject_MockObject $productMock)
    {
        $currencyMock = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects($this->any())
            ->method('getOutputFormat')
            ->willReturn('%s');

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods([
                'getCurrentCurrency',
            ])
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
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(\Magento\Catalog\Block\Product\Context::class)
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAmountMock($amount): \PHPUnit_Framework_MockObject_MockObject
    {
        $amountMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\AmountInterface::class)
            ->setMethods([
                'getValue',
                'getBaseAmount',
            ])
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
     * @param \PHPUnit_Framework_MockObject_MockObject $amountMock
     * @param float $priceQty
     * @param int $percentage
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTierPriceMock(\PHPUnit_Framework_MockObject_MockObject $amountMock, $priceQty, $percentage)
    {
        $tierPrice = [
            'price_qty' => $priceQty,
            'price' => $amountMock,
        ];

        $tierPriceMock = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\TierPriceInterface::class)
            ->setMethods([
                'getTierPriceList',
                'getSavePercent',
            ])
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
