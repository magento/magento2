<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class CustomOptionsTest extends AbstractModifierTestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $productOptionsConfigMock;

    /**
     * @var ProductOptionsPrice|MockObject
     */
    protected $productOptionsPriceMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = new ObjectManager($this);
        $this->productOptionsConfigMock = $this->createMock(ConfigInterface::class);
        $this->productOptionsPriceMock = $this->createMock(ProductOptionsPrice::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->storeMock = $this->createPartialMock(Store::class, ['getBaseCurrency']);
        $this->storeMock->method('getBaseCurrency')->willReturn($this->priceCurrency);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        
        // Configure productMock to handle getOptions properly
        $productState = new \stdClass();
        $productState->options = [];
        
        $this->productMock->productState = $productState;
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(CustomOptions::class, [
            'locator' => $this->locatorMock,
            'productOptionsConfig' => $this->productOptionsConfigMock,
            'productOptionsPrice' => $this->productOptionsPriceMock,
            'storeManager' => $this->storeManagerMock
        ]);
    }

    public function testModifyData()
    {
        $productId = 111;

        $originalData = [
            $productId => [
                CustomOptions::DATA_SOURCE_DEFAULT => [
                    'title' => 'original'
                ]
            ]
        ];

        $options = [
            $this->getProductOptionMock(['title' => 'option1', 'store_title' => 'Option Store Title']),
            $this->getProductOptionMock(
                ['title' => 'option2', 'store_title' => null],
                [
                    $this->getProductOptionMock(['title' => 'value1', 'store_title' => 'Option Value Store Title']),
                    $this->getProductOptionMock(['title' => 'value2', 'store_title' => null])
                ]
            )
        ];

        $resultData = [
            $productId => [
                CustomOptions::DATA_SOURCE_DEFAULT => [
                    CustomOptions::FIELD_TITLE_NAME => 'original',
                    CustomOptions::FIELD_ENABLE => 1,
                    CustomOptions::GRID_OPTIONS_NAME => [
                        [
                            CustomOptions::FIELD_TITLE_NAME => 'option1',
                            CustomOptions::FIELD_STORE_TITLE_NAME => 'Option Store Title',
                            CustomOptions::FIELD_IS_USE_DEFAULT => false
                        ], [
                            CustomOptions::FIELD_TITLE_NAME => 'option2',
                            CustomOptions::FIELD_STORE_TITLE_NAME => null,
                            CustomOptions::FIELD_IS_USE_DEFAULT => true,
                            CustomOptions::GRID_TYPE_SELECT_NAME => [
                                [
                                    CustomOptions::FIELD_TITLE_NAME => 'value1',
                                    CustomOptions::FIELD_STORE_TITLE_NAME => 'Option Value Store Title',
                                    CustomOptions::FIELD_IS_USE_DEFAULT => false
                                ], [
                                    CustomOptions::FIELD_TITLE_NAME => 'value2',
                                    CustomOptions::FIELD_STORE_TITLE_NAME => null,
                                    CustomOptions::FIELD_IS_USE_DEFAULT => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Set product ID and options
        $this->productMock->setId($productId);
        $this->productMock->productState->options = $options;
        
        // Configure getOptions to return from state
        $this->productMock->method('getOptions')->willReturnCallback(function () {
            return $this->productMock->productState->options;
        });

        $this->assertSame($resultData, $this->getModel()->modifyData($originalData));
    }

    public function testModifyMeta()
    {
        $this->priceCurrency->method('getCurrencySymbol')->willReturn('$');
        $this->productOptionsConfigMock->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $meta = $this->getModel()->modifyMeta([]);

        $this->assertArrayHasKey(CustomOptions::GROUP_CUSTOM_OPTIONS_NAME, $meta);

        $buttonAdd = $meta['custom_options']['children']['container_header']['children']['button_add'];
        $buttonAddTargetName = $buttonAdd['arguments']['data']['config']['actions'][0]['targetName'];
        $expectedTargetName = '${ $.ns }.${ $.ns }.' . CustomOptions::GROUP_CUSTOM_OPTIONS_NAME
            . '.' . CustomOptions::GRID_OPTIONS_NAME;

        $this->assertEquals($expectedTargetName, $buttonAddTargetName);
    }

    /**
     * Tests if Compatible File Extensions is required when Option Type "File" is selected in Customizable Options.
     */
    public function testFileExtensionRequired()
    {
        $this->productOptionsConfigMock->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $meta = $this->getModel()->modifyMeta([]);

        $config = $meta['custom_options']['children']['options']['children']['record']['children']['container_option']
        ['children']['container_type_static']['children']['file_extension']['arguments']['data']['config'];

        $scope = $config['dataScope'];
        $required = $config['validation']['required-entry'];

        $this->assertEquals(CustomOptions::FIELD_FILE_EXTENSION_NAME, $scope);
        $this->assertTrue($required);
    }

    /**
     * Get ProductOption mock object
     *
     * @param array $data
     * @param array $values
     * @return \Magento\Catalog\Model\Product\Option|MockObject
     */
    protected function getProductOptionMock(array $data, array $values = [])
    {
        /** @var ProductOption|MockObject $productOptionMock */
        $productOptionMock = $this->createPartialMock(ProductOption::class, ['getValues']);

        $productOptionMock->setData($data);
        $productOptionMock->method('getValues')->willReturn($values);

        return $productOptionMock;
    }

    /**
     * Test formatPriceByPath preserves original decimal precision
     *
     * @dataProvider formatPriceByPathDataProvider
     * @param mixed $inputValue
     * @param mixed $expectedValue
     */
    public function testFormatPriceByPath($inputValue, $expectedValue): void
    {
        $path = 'price';
        $data = ['price' => $inputValue];

        // Create model with real ArrayManager for proper testing
        $model = $this->objectManager->getObject(CustomOptions::class, [
            'locator' => $this->locatorMock,
            'productOptionsConfig' => $this->productOptionsConfigMock,
            'productOptionsPrice' => $this->productOptionsPriceMock,
            'storeManager' => $this->storeManagerMock,
            'arrayManager' => new ArrayManager()
        ]);

        $reflection = new \ReflectionClass($model);
        $method = $reflection->getMethod('formatPriceByPath');
        $method->setAccessible(true);

        $result = $method->invoke($model, $path, $data);

        $this->assertSame($expectedValue, $result['price']);
    }

    /**
     * Data provider for testFormatPriceByPath
     *
     * @return array
     */
    public static function formatPriceByPathDataProvider(): array
    {
        return [
            'three_decimals' => [
                'inputValue' => 2.334,
                'expectedValue' => '2.334'
            ],
            'four_decimals' => [
                'inputValue' => 2.3344,
                'expectedValue' => '2.3344'
            ],
            'five_decimals' => [
                'inputValue' => 10.44435,
                'expectedValue' => '10.44435'
            ],
            'six_decimals' => [
                'inputValue' => 10.444356,
                'expectedValue' => '10.444356'
            ],
            'seven_decimals_truncated_to_six' => [
                'inputValue' => 10.4443567,
                'expectedValue' => '10.444357'
            ],
            'two_decimals' => [
                'inputValue' => 5.25,
                'expectedValue' => '5.25'
            ],
            'one_decimal' => [
                'inputValue' => 5.5,
                'expectedValue' => '5.50'
            ],
            'integer_value' => [
                'inputValue' => 10,
                'expectedValue' => '10.00'
            ],
            'string_numeric' => [
                'inputValue' => '2.334',
                'expectedValue' => '2.334'
            ],
            'non_numeric_string' => [
                'inputValue' => 'not_a_number',
                'expectedValue' => 'not_a_number'
            ],
            'null_value' => [
                'inputValue' => null,
                'expectedValue' => null
            ],
            'empty_string' => [
                'inputValue' => '',
                'expectedValue' => ''
            ],
            'zero_value' => [
                'inputValue' => 0,
                'expectedValue' => '0.00'
            ],
            'zero_with_decimals' => [
                'inputValue' => 0.00,
                'expectedValue' => '0.00'
            ],
            'negative_value' => [
                'inputValue' => -2.334,
                'expectedValue' => '-2.334'
            ]
        ];
    }

    /**
     * Test getCommonContainerConfig does not add service template when storeId is 0 (default store)
     */
    public function testGetCommonContainerConfigWithDefaultStore(): void
    {
        $sortOrder = 10;

        // Configure product mock to return storeId 0 (default store)
        $this->productMock->method('getStoreId')->willReturn(0);

        // Configure productOptionsConfig to return empty array
        $this->productOptionsConfigMock->method('getAll')->willReturn([]);

        $model = $this->objectManager->getObject(CustomOptions::class, [
            'locator' => $this->locatorMock,
            'productOptionsConfig' => $this->productOptionsConfigMock,
            'productOptionsPrice' => $this->productOptionsPriceMock,
            'storeManager' => $this->storeManagerMock,
            'arrayManager' => new ArrayManager()
        ]);

        $reflection = new \ReflectionClass($model);
        $method = $reflection->getMethod('getCommonContainerConfig');
        $method->setAccessible(true);

        $result = $method->invoke($model, $sortOrder);

        // Verify the title field does not have the service template
        $titleConfig = $result['children'][CustomOptions::FIELD_TITLE_NAME]['arguments']['data']['config'] ?? [];
        $this->assertArrayNotHasKey('service', $titleConfig);
    }

    /**
     * Test getCommonContainerConfig adds service template when storeId is non-zero (store view)
     */
    public function testGetCommonContainerConfigWithStoreView(): void
    {
        $sortOrder = 10;
        $storeId = 1;

        // Configure product mock to return non-zero storeId
        $this->productMock->method('getStoreId')->willReturn($storeId);

        // Configure productOptionsConfig to return empty array
        $this->productOptionsConfigMock->method('getAll')->willReturn([]);

        $model = $this->objectManager->getObject(CustomOptions::class, [
            'locator' => $this->locatorMock,
            'productOptionsConfig' => $this->productOptionsConfigMock,
            'productOptionsPrice' => $this->productOptionsPriceMock,
            'storeManager' => $this->storeManagerMock,
            'arrayManager' => new ArrayManager()
        ]);

        $reflection = new \ReflectionClass($model);
        $method = $reflection->getMethod('getCommonContainerConfig');
        $method->setAccessible(true);

        $result = $method->invoke($model, $sortOrder);

        // Verify the title field has the service template for "Use Default" functionality
        $titleConfig = $result['children'][CustomOptions::FIELD_TITLE_NAME]['arguments']['data']['config'] ?? [];
        $this->assertArrayHasKey('service', $titleConfig);
        $this->assertEquals(
            'Magento_Catalog/form/element/helper/custom-option-service',
            $titleConfig['service']['template']
        );
    }

    /**
     * Test getProductOptionTypes with enabled and disabled types
     *
     * @dataProvider getProductOptionTypesDataProvider
     * @param array $optionsConfig
     * @param array $expectedResult
     */
    public function testGetProductOptionTypes(array $optionsConfig, array $expectedResult): void
    {
        $this->productOptionsConfigMock->method('getAll')->willReturn($optionsConfig);

        $model = $this->objectManager->getObject(CustomOptions::class, [
            'locator' => $this->locatorMock,
            'productOptionsConfig' => $this->productOptionsConfigMock,
            'productOptionsPrice' => $this->productOptionsPriceMock,
            'storeManager' => $this->storeManagerMock,
            'arrayManager' => new ArrayManager()
        ]);

        $reflection = new \ReflectionClass($model);
        $method = $reflection->getMethod('getProductOptionTypes');
        $method->setAccessible(true);

        $result = $method->invoke($model);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testGetProductOptionTypes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getProductOptionTypesDataProvider(): array
    {
        return [
            'empty_options' => [
                'optionsConfig' => [],
                'expectedResult' => []
            ],
            'single_group_with_enabled_types' => [
                'optionsConfig' => [
                    [
                        'label' => 'Text',
                        'types' => [
                            ['label' => 'Field', 'name' => 'field', 'disabled' => false],
                            ['label' => 'Area', 'name' => 'area', 'disabled' => false]
                        ]
                    ]
                ],
                'expectedResult' => [
                    [
                        'value' => 0,
                        'label' => 'Text',
                        'optgroup' => [
                            ['label' => 'Field', 'value' => 'field'],
                            ['label' => 'Area', 'value' => 'area']
                        ]
                    ]
                ]
            ],
            'single_group_with_disabled_type' => [
                'optionsConfig' => [
                    [
                        'label' => 'Text',
                        'types' => [
                            ['label' => 'Field', 'name' => 'field', 'disabled' => false],
                            ['label' => 'Area', 'name' => 'area', 'disabled' => true]
                        ]
                    ]
                ],
                'expectedResult' => [
                    [
                        'value' => 0,
                        'label' => 'Text',
                        'optgroup' => [
                            ['label' => 'Field', 'value' => 'field']
                        ]
                    ]
                ]
            ],
            'group_with_all_disabled_types_should_be_excluded' => [
                'optionsConfig' => [
                    [
                        'label' => 'Text',
                        'types' => [
                            ['label' => 'Field', 'name' => 'field', 'disabled' => true],
                            ['label' => 'Area', 'name' => 'area', 'disabled' => true]
                        ]
                    ]
                ],
                'expectedResult' => []
            ],
            'multiple_groups_with_mixed_types' => [
                'optionsConfig' => [
                    [
                        'label' => 'Text',
                        'types' => [
                            ['label' => 'Field', 'name' => 'field', 'disabled' => false],
                            ['label' => 'Area', 'name' => 'area', 'disabled' => true]
                        ]
                    ],
                    [
                        'label' => 'Select',
                        'types' => [
                            ['label' => 'Drop-down', 'name' => 'drop_down', 'disabled' => false],
                            ['label' => 'Radio Buttons', 'name' => 'radio', 'disabled' => false]
                        ]
                    ],
                    [
                        'label' => 'Date',
                        'types' => [
                            ['label' => 'Date', 'name' => 'date', 'disabled' => true]
                        ]
                    ]
                ],
                'expectedResult' => [
                    [
                        'value' => 0,
                        'label' => 'Text',
                        'optgroup' => [
                            ['label' => 'Field', 'value' => 'field']
                        ]
                    ],
                    [
                        'value' => 1,
                        'label' => 'Select',
                        'optgroup' => [
                            ['label' => 'Drop-down', 'value' => 'drop_down'],
                            ['label' => 'Radio Buttons', 'value' => 'radio']
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Test formatPriceValue method directly
     *
     * @dataProvider formatPriceValueDataProvider
     * @param mixed $inputValue
     * @param string $expectedValue
     */
    public function testFormatPriceValue($inputValue, string $expectedValue): void
    {
        $model = $this->objectManager->getObject(CustomOptions::class, [
            'locator' => $this->locatorMock,
            'productOptionsConfig' => $this->productOptionsConfigMock,
            'productOptionsPrice' => $this->productOptionsPriceMock,
            'storeManager' => $this->storeManagerMock,
            'arrayManager' => new ArrayManager()
        ]);

        $reflection = new \ReflectionClass($model);
        $method = $reflection->getMethod('formatPriceValue');
        $method->setAccessible(true);

        $result = $method->invoke($model, $inputValue);

        $this->assertSame($expectedValue, $result);
    }

    /**
     * Data provider for testFormatPriceValue
     *
     * @return array
     */
    public static function formatPriceValueDataProvider(): array
    {
        return [
            'null_value_returns_empty_string' => [
                'inputValue' => null,
                'expectedValue' => ''
            ],
            'integer_value' => [
                'inputValue' => 10,
                'expectedValue' => '10.00'
            ],
            'float_with_decimals' => [
                'inputValue' => 2.334,
                'expectedValue' => '2.334'
            ],
            'float_exceeding_max_precision' => [
                'inputValue' => 10.4443567,
                'expectedValue' => '10.444357'
            ],
            'zero_value' => [
                'inputValue' => 0,
                'expectedValue' => '0.00'
            ],
            'string_numeric' => [
                'inputValue' => '5.25',
                'expectedValue' => '5.25'
            ]
        ];
    }
}
