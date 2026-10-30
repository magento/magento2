<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Plugin\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableBlock;
use Magento\Framework\DataObject;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Plugin\ConfigurableProduct\Block\Product\View\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for WEEE Configurable Product Plugin
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var Configurable
     */
    private $plugin;

    /**
     * @var WeeeHelper|MockObject
     */
    private $weeeHelperMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private $jsonEncoderMock;

    /**
     * @var DecoderInterface|MockObject
     */
    private $jsonDecoderMock;

    /**
     * @var ConfigurableBlock|MockObject
     */
    private $subjectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->weeeHelperMock = $this->createMock(WeeeHelper::class);
        $this->jsonEncoderMock = $this->createMock(EncoderInterface::class);
        $this->jsonDecoderMock = $this->createMock(DecoderInterface::class);
        $this->subjectMock = $this->createMock(ConfigurableBlock::class);

        $this->plugin = new Configurable(
            $this->weeeHelperMock,
            $this->jsonEncoderMock,
            $this->jsonDecoderMock
        );
    }

    /**
     * Test afterGetJsonConfig when WEEE is disabled
     *
     * @return void
     */
    public function testAfterGetJsonConfigWhenWeeeDisabled(): void
    {
        $jsonConfig = '{"optionPrices":{"1":{"finalPrice":{"amount":100}}}}';

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn(['optionPrices' => ['1' => ['finalPrice' => ['amount' => 100]]]]);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->jsonEncoderMock->expects($this->never())
            ->method('encode');

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertEquals($jsonConfig, $result);
    }

    /**
     * Test afterGetJsonConfig when optionPrices is empty
     *
     * @return void
     */
    public function testAfterGetJsonConfigWhenOptionPricesEmpty(): void
    {
        $jsonConfig = '{"optionPrices":{}}';

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn(['optionPrices' => []]);

        $this->weeeHelperMock->expects($this->never())
            ->method('isEnabled');

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertEquals($jsonConfig, $result);
    }

    /**
     * Test afterGetJsonConfig when config is null
     *
     * @return void
     */
    public function testAfterGetJsonConfigWhenConfigIsNull(): void
    {
        $jsonConfig = 'null';

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn(null);

        $this->weeeHelperMock->expects($this->never())
            ->method('isEnabled');

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertEquals($jsonConfig, $result);
    }

    /**
     * Test afterGetJsonConfig when products have no WEEE attributes
     *
     * @return void
     */
    public function testAfterGetJsonConfigWhenNoWeeeAttributes(): void
    {
        $jsonConfig = '{"optionPrices":{"1":{"finalPrice":{"amount":100}}},"priceFormat":{}}';
        $config = [
            'optionPrices' => [
                '1' => ['finalPrice' => ['amount' => 100]]
            ],
            'priceFormat' => []
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn($config);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->subjectMock->expects($this->once())
            ->method('getAllowProducts')
            ->willReturn([$productMock]);

        $this->weeeHelperMock->expects($this->once())
            ->method('getProductWeeeAttributesForDisplay')
            ->with($productMock)
            ->willReturn([]);

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($config)
            ->willReturn($jsonConfig);

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertEquals($jsonConfig, $result);
    }

    /**
     * Test afterGetJsonConfig when product is not in optionPrices
     *
     * @return void
     */
    public function testAfterGetJsonConfigWhenProductNotInOptionPrices(): void
    {
        $jsonConfig = '{"optionPrices":{"1":{"finalPrice":{"amount":100}}},"priceFormat":{}}';
        $config = [
            'optionPrices' => [
                '1' => ['finalPrice' => ['amount' => 100]]
            ],
            'priceFormat' => []
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(999); // Different product ID

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn($config);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->subjectMock->expects($this->once())
            ->method('getAllowProducts')
            ->willReturn([$productMock]);

        $this->weeeHelperMock->expects($this->never())
            ->method('getProductWeeeAttributesForDisplay');

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($config)
            ->willReturn($jsonConfig);

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertEquals($jsonConfig, $result);
    }

    /**
     * Test afterGetJsonConfig with WEEE attributes
     *
     * @return void
     */
    public function testAfterGetJsonConfigWithWeeeAttributes(): void
    {
        $jsonConfig = '{"optionPrices":{"1":{"finalPrice":{"amount":110.50}}},'
            . '"priceFormat":{"pattern":"$%s","precision":2,"decimalSymbol":".","groupSymbol":","}}';
        $config = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => ['amount' => 110.50]
                ]
            ],
            'priceFormat' => [
                'pattern' => '$%s',
                'precision' => 2,
                'decimalSymbol' => '.',
                'groupSymbol' => ','
            ]
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $weeeAttributeMock = new DataObject([
            'amount' => 10.50,
            'name' => 'FPT Tax',
            'amount_excl_tax' => 10.00,
            'tax_amount' => 0.50
        ]);

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn($config);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->subjectMock->expects($this->once())
            ->method('getAllowProducts')
            ->willReturn([$productMock]);

        $this->weeeHelperMock->expects($this->once())
            ->method('getProductWeeeAttributesForDisplay')
            ->with($productMock)
            ->willReturn([$weeeAttributeMock]);

        $expectedConfig = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => [
                        'amount' => 110.50,
                        'weeeAmount' => 10.50,
                        'weeeAttributes' => [
                            [
                                'name' => 'FPT Tax',
                                'amount' => 10.50,
                                'formatted' => '$10.50'
                            ]
                        ],
                        'amountWithoutWeee' => 100.00,
                        'formattedWithoutWeee' => '$100.00',
                        'formattedWithWeee' => '$110.50'
                    ]
                ]
            ],
            'priceFormat' => [
                'pattern' => '$%s',
                'precision' => 2,
                'decimalSymbol' => '.',
                'groupSymbol' => ','
            ]
        ];

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($expectedConfig)
            ->willReturn(json_encode($expectedConfig));

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertIsString($result);
    }

    /**
     * Test afterGetJsonConfig with multiple WEEE attributes
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterGetJsonConfigWithMultipleWeeeAttributes(): void
    {
        $jsonConfig = '{"optionPrices":{"1":{"finalPrice":{"amount":125.75}}},'
            . '"priceFormat":{"pattern":"$%s","precision":2,"decimalSymbol":".","groupSymbol":","}}';
        $config = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => ['amount' => 125.75]
                ]
            ],
            'priceFormat' => [
                'pattern' => '$%s',
                'precision' => 2,
                'decimalSymbol' => '.',
                'groupSymbol' => ','
            ]
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $weeeAttribute1Mock = new DataObject([
            'amount' => 10.50,
            'name' => 'FPT Tax 1',
            'amount_excl_tax' => 10.00,
            'tax_amount' => 0.50
        ]);

        $weeeAttribute2Mock = new DataObject([
            'amount' => 15.25,
            'name' => 'FPT Tax 2',
            'amount_excl_tax' => 14.50,
            'tax_amount' => 0.75
        ]);

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn($config);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->subjectMock->expects($this->once())
            ->method('getAllowProducts')
            ->willReturn([$productMock]);

        $this->weeeHelperMock->expects($this->once())
            ->method('getProductWeeeAttributesForDisplay')
            ->with($productMock)
            ->willReturn([$weeeAttribute1Mock, $weeeAttribute2Mock]);

        $expectedConfig = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => [
                        'amount' => 125.75,
                        'weeeAmount' => 25.75,
                        'weeeAttributes' => [
                            [
                                'name' => 'FPT Tax 1',
                                'amount' => 10.50,
                                'formatted' => '$10.50'
                            ],
                            [
                                'name' => 'FPT Tax 2',
                                'amount' => 15.25,
                                'formatted' => '$15.25'
                            ]
                        ],
                        'amountWithoutWeee' => 100.00,
                        'formattedWithoutWeee' => '$100.00',
                        'formattedWithWeee' => '$125.75'
                    ]
                ]
            ],
            'priceFormat' => [
                'pattern' => '$%s',
                'precision' => 2,
                'decimalSymbol' => '.',
                'groupSymbol' => ','
            ]
        ];

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($expectedConfig)
            ->willReturn(json_encode($expectedConfig));

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertIsString($result);
    }

    /**
     * Test afterGetJsonConfig with WEEE attribute without name
     *
     * @return void
     */
    public function testAfterGetJsonConfigWithWeeeAttributeWithoutName(): void
    {
        $jsonConfig = '{"optionPrices":{"1":{"finalPrice":{"amount":110.50}}},'
            . '"priceFormat":{"pattern":"$%s","precision":2,"decimalSymbol":".","groupSymbol":","}}';
        $config = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => ['amount' => 110.50]
                ]
            ],
            'priceFormat' => [
                'pattern' => '$%s',
                'precision' => 2,
                'decimalSymbol' => '.',
                'groupSymbol' => ','
            ]
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $weeeAttributeMock = new DataObject([
            'amount' => 10.50,
            'amount_excl_tax' => 10.00,
            'tax_amount' => 0.50
            // No 'name' key = getData('name') returns null
        ]);

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn($config);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->subjectMock->expects($this->once())
            ->method('getAllowProducts')
            ->willReturn([$productMock]);

        $this->weeeHelperMock->expects($this->once())
            ->method('getProductWeeeAttributesForDisplay')
            ->with($productMock)
            ->willReturn([$weeeAttributeMock]);

        $expectedConfig = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => [
                        'amount' => 110.50,
                        'weeeAmount' => 10.50,
                        'weeeAttributes' => [
                            [
                                'name' => 'FPT', // Default name
                                'amount' => 10.50,
                                'formatted' => '$10.50'
                            ]
                        ],
                        'amountWithoutWeee' => 100.00,
                        'formattedWithoutWeee' => '$100.00',
                        'formattedWithWeee' => '$110.50'
                    ]
                ]
            ],
            'priceFormat' => [
                'pattern' => '$%s',
                'precision' => 2,
                'decimalSymbol' => '.',
                'groupSymbol' => ','
            ]
        ];

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($expectedConfig)
            ->willReturn(json_encode($expectedConfig));

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertIsString($result);
    }

    /**
     * Test price formatting with different formats
     *
     * @return void
     */
    public function testAfterGetJsonConfigWithDifferentPriceFormat(): void
    {
        $jsonConfig = '{"optionPrices":{"1":{"finalPrice":{"amount":1234.567}}},'
            . '"priceFormat":{"pattern":"%s €","precision":3,"decimalSymbol":",","groupSymbol":"."}}';
        $config = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => ['amount' => 1234.567]
                ]
            ],
            'priceFormat' => [
                'pattern' => '%s €',
                'precision' => 3,
                'decimalSymbol' => ',',
                'groupSymbol' => '.'
            ]
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $weeeAttributeMock = new DataObject([
            'amount' => 234.567,
            'name' => 'Euro Tax',
            'amount_excl_tax' => 234.00,
            'tax_amount' => 0.567
        ]);

        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn($config);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->subjectMock->expects($this->once())
            ->method('getAllowProducts')
            ->willReturn([$productMock]);

        $this->weeeHelperMock->expects($this->once())
            ->method('getProductWeeeAttributesForDisplay')
            ->with($productMock)
            ->willReturn([$weeeAttributeMock]);

        $expectedConfig = [
            'optionPrices' => [
                '1' => [
                    'finalPrice' => [
                        'amount' => 1234.567,
                        'weeeAmount' => 234.567,
                        'weeeAttributes' => [
                            [
                                'name' => 'Euro Tax',
                                'amount' => 234.567,
                                'formatted' => '234,567 €'
                            ]
                        ],
                        'amountWithoutWeee' => 1000.00,
                        'formattedWithoutWeee' => '1.000,000 €',
                        'formattedWithWeee' => '1.234,567 €'
                    ]
                ]
            ],
            'priceFormat' => [
                'pattern' => '%s €',
                'precision' => 3,
                'decimalSymbol' => ',',
                'groupSymbol' => '.'
            ]
        ];

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($expectedConfig)
            ->willReturn(json_encode($expectedConfig));

        $result = $this->plugin->afterGetJsonConfig($this->subjectMock, $jsonConfig);

        $this->assertIsString($result);
    }
}
