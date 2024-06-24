<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data as CatalogConfig;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\ImportExport\Helper\Data as ImportExportConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class ProductOptionsTest extends ProductTestBase
{
    /**
     * Options for assertion
     *
     * @var array
     */
    protected $_assertOptions = [
        'is_require' => 'required',
        'price' => 'price',
        'sku' => 'sku',
        'sort_order' => 'order',
        'max_characters' => 'max_characters',
    ];

    /**
     * Option values for assertion
     *
     * @var array
     */
    protected $_assertOptionValues = [
        'title' => 'option_title',
        'price' => 'price',
        'sku' => 'sku',
    ];

    /**
     * List of specific custom option types
     *
     * @var array
     */
    protected $specificTypes = [
        'drop_down',
        'radio',
        'checkbox',
        'multiple',
    ];

    /**
     * Tests adding of custom options with existing and new product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getBehaviorDataProvider
     * @param string $importFile
     * @param string $sku
     * @param int $expectedOptionsQty
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
    public function testSaveCustomOptions(string $importFile, string $sku, int $expectedOptionsQty): void
    {
        $pathToFile = __DIR__ . '/../_files/' . $importFile;
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $importModel->importData();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $product = $productRepository->get($sku);

        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $product);
        $options = $product->getOptionInstance()->getProductOptions($product);

        $expectedData = $this->getExpectedOptionsData($pathToFile);
        $expectedData = $this->mergeWithExistingData($expectedData, $options);
        $actualData = $this->getActualOptionsData($options);

        // assert of equal type+titles
        $expectedOptions = $expectedData['options'];
        // we need to save key values
        $actualOptions = $actualData['options'];
        sort($expectedOptions);
        sort($actualOptions);
        $this->assertSame($expectedOptions, $actualOptions);

        // assert of options data
        $this->assertCount(count($expectedData['data']), $actualData['data']);
        $this->assertCount(count($expectedData['values']), $actualData['values']);
        $this->assertCount($expectedOptionsQty, $actualData['options']);
        foreach ($expectedData['options'] as $expectedId => $expectedOption) {
            $elementExist = false;
            // find value in actual options and values
            foreach ($actualData['options'] as $actualId => $actualOption) {
                if ($actualOption == $expectedOption) {
                    $elementExist = true;
                    $this->assertEquals($expectedData['data'][$expectedId], $actualData['data'][$actualId]);
                    if (array_key_exists($expectedId, $expectedData['values'])) {
                        $this->assertEquals($expectedData['values'][$expectedId], $actualData['values'][$actualId]);
                    }
                    unset($actualData['options'][$actualId]);
                    // remove value in case of duplicating key values
                    break;
                }
            }
            $this->assertTrue($elementExist, 'Element must exist.');
        }

        // Make sure that after importing existing options again, option IDs and option value IDs are not changed
        $customOptionValues = $this->getCustomOptionValues($sku);
        $importModel = $this->createImportModel($pathToFile);
        $importModel->validateData();
        $importModel->importData();
        $this->assertEquals($customOptionValues, $this->getCustomOptionValues($sku));

        // Cleanup imported products
        try {
            $this->productRepository->delete($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
    }

    /**
     * Tests adding of custom options with multiple store views
     *
     * @dataProvider saveCustomOptionsWithMultipleStoreViewsDataProvider
     * @param string $importFile
     * @param array $expected
     */
    #[
        AppIsolation(true),
        Config(CatalogConfig::XML_PATH_PRICE_SCOPE, CatalogConfig::PRICE_SCOPE_WEBSITE, ScopeInterface::SCOPE_STORE),
        DataFixture(StoreFixture::class, ['code' => 'secondstore']),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple2',
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                        'title' => 'Option 1',
                        'values' => [
                            [
                                'title'         => 'Option 1 Value 1',
                                'price'         => 2.5,
                                'sku'           => 'option1value1',
                            ],
                            [
                                'title'         => 'Option 1 Value 2',
                                'price'         => 3,
                                'sku'           => 'option1value2',
                            ],
                        ]
                    ]
                ]
            ]
        ),
    ]
    public function testSaveCustomOptionsWithMultipleStoreViews(
        string $importFile,
        array $expected
    ) {
        $expected = $this->getFullExpectedOptions($expected);
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $pathToFile = __DIR__ . '/../_files/' . $importFile;
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0, 'Import File Validation Failed');
        $importModel->importData();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(
            ProductRepositoryInterface::class
        );
        $actual = [];
        foreach ($expected as $sku => $storesData) {
            foreach (array_keys($storesData) as $storeCode) {
                $product = $productRepository->get($sku, false, $storeManager->getStore($storeCode)->getId(), true);
                $options = $product->getOptionInstance()->getProductOptions($product);
                $actual[$sku][$storeCode] = [];
                /** @var $option \Magento\Catalog\Model\Product\Option */
                foreach ($options as $option) {
                    $optionData = [
                        'type' => $option->getType(),
                        'title' => $option->getTitle()
                    ];
                    $optionData += $this->getOptionData($option);
                    if (in_array($option->getType(), $this->specificTypes)) {
                        $optionData['values'] = $this->getOptionValues($option);
                    }
                    $actual[$sku][$storeCode][] = $optionData;
                }
            }
        }

        $this->assertEquals($expected, $actual);

        // Make sure that after importing existing options again, option IDs and option value IDs are not changed
        $expectedIds = [];
        $actualIds = [];
        foreach (array_keys($expected) as $sku) {
            $expectedIds[$sku] = $this->getCustomOptionValues($sku);
        }
        $importModel = $this->createImportModel($pathToFile);
        $importModel->validateData();
        $importModel->importData();
        foreach (array_keys($expected) as $sku) {
            $actualIds[$sku] = $this->getCustomOptionValues($sku);

        }

        $this->assertEquals(
            $expectedIds,
            $actualIds,
            'Option IDs changed after second import'
        );
    }

    /**
     * Tests adding of custom options with multiple store views across bunches
     *
     * @dataProvider saveCustomOptionsWithMultipleStoreViewsDataProvider
     * @param string $importFile
     * @param array $expected
     */
    #[
        AppIsolation(true),
        Config(CatalogConfig::XML_PATH_PRICE_SCOPE, CatalogConfig::PRICE_SCOPE_WEBSITE, ScopeInterface::SCOPE_STORE),
        Config(ImportExportConfig::XML_PATH_BUNCH_SIZE, 2, ScopeInterface::SCOPE_STORE),
        DataFixture(StoreFixture::class, ['code' => 'secondstore']),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple2',
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                        'title' => 'Option 1',
                        'values' => [
                            [
                                'title'         => 'Option 1 Value 1',
                                'price'         => 2.5,
                                'sku'           => 'option1value1',
                            ],
                            [
                                'title'         => 'Option 1 Value 2',
                                'price'         => 3,
                                'sku'           => 'option1value2',
                            ],
                        ]
                    ]
                ]
            ]
        ),
    ]
    public function testSaveCustomOptionsWithMultipleStoreViewsAcrossMultipleBunches(
        string $importFile,
        array $expected
    ) {
        $this->testSaveCustomOptionsWithMultipleStoreViews($importFile, $expected);
    }

    /**
     * @return array
     */
    public static function getBehaviorDataProvider(): array
    {
        return [
            'Append behavior with existing product' => [
                'importFile' => 'product_with_custom_options.csv',
                'sku' => 'simple',
                'expectedOptionsQty' => 6,
            ],
            'Append behavior with existing product and without options in import file' => [
                'importFile' => 'product_without_custom_options.csv',
                'sku' => 'simple',
                'expectedOptionsQty' => 0,
            ],
            'Append behavior with new product' => [
                'importFile' => 'product_with_custom_options_new.csv',
                'sku' => 'simple_new',
                'expectedOptionsQty' => 5,
            ],
        ];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function saveCustomOptionsWithMultipleStoreViewsDataProvider(): array
    {
        return [
            [
                'product_with_custom_options_and_multiple_store_views.csv',
                [
                    'simple' => [
                        'admin' => [
                            [
                                'title' => 'Test Field Title',
                                'type' => 'field',
                                'is_require' => '1',
                                'sku' => '1-text',
                                'price' => '100.000000',
                                'max_characters' => '0',
                                'sort_order' => '1',
                            ],
                            [
                                'title' => 'Test Date and Time Title',
                                'type' => 'date_time',
                                'is_require' => '1',
                                'sku' => '2-date',
                                'price' => '200.000000',
                                'max_characters' => '0',
                                'sort_order' => '2',
                            ],
                            [
                                'title' => 'Test Select',
                                'type' => 'drop_down',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '0',
                                'sort_order' => '3',
                                'values' => [
                                    [
                                        'title' => 'Select Option 1',
                                        'sku' => '3-1-select',
                                        'price' => '310.000000',
                                    ],
                                    [
                                        'title' => 'Select Option 2',
                                        'sku' => '3-2-select',
                                        'price' => '320.000000',
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Test Checkbox',
                                'type' => 'checkbox',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '0',
                                'sort_order' => '4',
                                'values' => [
                                    [
                                        'title' => 'Checkbox Option 1',
                                        'sku' => '4-1-select',
                                        'price' => '410.000000',
                                    ],
                                    [
                                        'title' => 'Checkbox Option 2',
                                        'sku' => '4-2-select',
                                        'price' => '420.000000',
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Test Radio',
                                'type' => 'radio',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '0',
                                'sort_order' => '5',
                                'values' => [
                                    [
                                        'title' => 'Radio Option 1',
                                        'sku' => '5-1-radio',
                                        'price' => '510.000000',
                                    ],
                                    [
                                        'title' => 'Radio Option 2',
                                        'sku' => '5-2-radio',
                                        'price' => '520.000000',
                                    ]
                                ]
                            ]
                        ],
                        'default' => [
                            [
                                'title' => 'Test Field Title_default',
                            ],
                            [
                                'title' => 'Test Date and Time Title_default',
                            ],
                            [
                                'title' => 'Test Select_default',
                                'values' => [
                                    [
                                        'title' => 'Select Option 1_default',
                                    ],
                                    [
                                        'title' => 'Select Option 2_default',
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Test Checkbox_default',
                                'values' => [
                                    [
                                        'title' => 'Checkbox Option 1_default',
                                    ],
                                    [
                                        'title' => 'Checkbox Option 2_default',
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Test Radio_default',
                                'values' => [
                                    [
                                        'title' => 'Radio Option 1_default',
                                    ],
                                    [
                                        'title' => 'Radio Option 2_default',
                                    ]
                                ]
                            ]
                        ],
                        'secondstore' => [
                            [
                                'title' => 'Test Field Title_fixture_second_store',
                                'price' => '101.000000'
                            ],
                            [
                                'title' => 'Test Date and Time Title_fixture_second_store',
                                'price' => '201.000000'
                            ],
                            [
                                'title' => 'Test Select_fixture_second_store',
                                'values' => [
                                    [
                                        'title' => 'Select Option 1_fixture_second_store',
                                        'price' => '311.000000'
                                    ],
                                    [
                                        'title' => 'Select Option 2_fixture_second_store',
                                        'price' => '321.000000'
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Test Checkbox_second_store',
                                'values' => [
                                    [
                                        'title' => 'Checkbox Option 1_second_store',
                                        'price' => '411.000000'
                                    ],
                                    [
                                        'title' => 'Checkbox Option 2_second_store',
                                        'price' => '421.000000'
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Test Radio_fixture_second_store',
                                'values' => [
                                    [
                                        'title' => 'Radio Option 1_fixture_second_store',
                                        'price' => '511.000000'
                                    ],
                                    [
                                        'title' => 'Radio Option 2_fixture_second_store',
                                        'price' => '521.000000'
                                    ]
                                ]
                            ]
                        ],
                    ],
                    'newprod2' => [
                        'admin' => [],
                        'default' => [],
                        'secondstore' => [],
                    ],
                    'newprod3' => [
                        'admin' => [
                            [
                                'title' => 'Line 1',
                                'type' => 'field',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '1',
                            ],
                            [
                                'title' => 'Line 2',
                                'type' => 'field',
                                'is_require' => '0',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '2',
                            ],
                        ],
                        'default' => [
                            [
                                'title' => 'Line 1',
                                'type' => 'field',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '1',
                            ],
                            [
                                'title' => 'Line 2',
                                'type' => 'field',
                                'is_require' => '0',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '2',
                            ],
                        ],
                        'secondstore' => [
                            [
                                'title' => 'Line 1',
                                'type' => 'field',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '1',
                            ],
                            [
                                'title' => 'Line 2',
                                'type' => 'field',
                                'is_require' => '0',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '2',
                            ],
                        ],
                    ],
                    'newprod4' => [
                        'admin' => [],
                        'default' => [],
                        'secondstore' => [],
                    ],
                    'newprod5' => [
                        'admin' => [
                            [
                                'title' => 'Line 3',
                                'type' => 'field',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '1',
                            ],
                            [
                                'title' => 'Line 4',
                                'type' => 'field',
                                'is_require' => '0',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '2',
                            ],
                        ],
                        'default' => [
                            [
                                'title' => 'Line 3',
                                'type' => 'field',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '1',
                            ],
                            [
                                'title' => 'Line 4',
                                'type' => 'field',
                                'is_require' => '0',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '2',
                            ],
                        ],
                        'secondstore' => [
                            [
                                'title' => 'Line 3',
                                'type' => 'field',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '1',
                            ],
                            [
                                'title' => 'Line 4',
                                'type' => 'field',
                                'is_require' => '0',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '30',
                                'sort_order' => '2',
                            ],
                        ],
                    ],
                    'simple2' => [
                        'admin' => [
                            [
                                'title' => 'Option 1',
                                'type' => 'drop_down',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '0',
                                'sort_order' => '1',
                                'values' => [
                                    [
                                        'title' => 'Option 1 Value 1',
                                        'sku' => 'option1value1',
                                        'price' => '1.200000',
                                    ],
                                    [
                                        'title' => 'Option 1 Value 2',
                                        'sku' => 'option1value2',
                                        'price' => '1.400000',
                                    ]
                                ]
                            ]
                        ],
                        'default' => [
                            [
                                'title' => 'Option 1 Store1',
                                'type' => 'drop_down',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '0',
                                'sort_order' => '1',
                                'values' => [
                                    [
                                        'title' => 'Option 1 Value 1 Store1',
                                        'sku' => 'option1value1',
                                        'price' => '1.100000',
                                    ],
                                    [
                                        'title' => 'Option 1 Value 2 Store1',
                                        'sku' => 'option1value2',
                                        'price' => '1.300000',
                                    ]
                                ]
                            ]
                        ],
                        'secondstore' => [
                            [
                                'title' => 'Option 1 Store2',
                                'type' => 'drop_down',
                                'is_require' => '1',
                                'sku' => '',
                                'price' => null,
                                'max_characters' => '0',
                                'sort_order' => '1',
                                'values' => [
                                    [
                                        'title' => 'Option 1 Value 1 Store2',
                                        'sku' => 'option1value1',
                                        'price' => '1.000000',
                                    ],
                                    [
                                        'title' => 'Option 1 Value 2 Store2',
                                        'sku' => 'option1value2',
                                        'price' => '1.200000',
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @param string $productSku
     * @return array ['optionId' => ['optionValueId' => 'optionValueTitle', ...], ...]
     */
    protected function getCustomOptionValues($productSku)
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductCustomOptionRepositoryInterface $customOptionRepository */
        $customOptionRepository = $this->objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $simpleProduct = $productRepository->get($productSku, false, null, true);
        $originalProductOptions = $customOptionRepository->getProductOptions($simpleProduct);
        $optionValues = [];
        foreach ($originalProductOptions as $productOption) {
            foreach ((array)$productOption->getValues() as $optionValue) {
                $optionValues[$productOption->getOptionId()][$optionValue->getOptionTypeId()]
                    = $optionValue->getTitle();
            }
        }
        return $optionValues;
    }

    /**
     * Returns expected product data: current id, options, options data and option values
     *
     * @param string $pathToFile
     * @param string $storeCode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function getExpectedOptionsData(string $pathToFile, string $storeCode = ''): array
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $productData = $this->csvToArray(file_get_contents($pathToFile));
        $expectedOptionId = 0;
        $expectedOptions = [];
        // array of type and title types, key is element ID
        $expectedData = [];
        // array of option data
        $expectedValues = [];
        $storeRowId = null;
        foreach ($productData['data'] as $rowId => $rowData) {
            $storeCode = ($storeCode == 'admin') ? '' : $storeCode;
            if ($rowData['store_view_code'] == $storeCode) {
                $storeRowId = $rowId;
                break;
            }
        }
        if (!empty($productData['data'][$storeRowId]['custom_options'])) {
            foreach (explode('|', $productData['data'][$storeRowId]['custom_options']) as $optionData) {
                $option = array_values(
                    array_map(
                        function ($input) {
                            $data = explode('=', $input);
                            return [$data[0] => $data[1]];
                        },
                        explode(',', $optionData)
                    )
                );
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $option = array_merge([], ...$option);

                if (!empty($option['type']) && !empty($option['name'])) {
                    $lastOptionKey = $option['type'] . '|' . $option['name'];
                    if (!isset($expectedOptions[$expectedOptionId])
                        || $expectedOptions[$expectedOptionId] != $lastOptionKey) {
                        $expectedOptionId++;
                        $expectedOptions[$expectedOptionId] = $lastOptionKey;
                        $expectedData[$expectedOptionId] = [];
                        foreach ($this->_assertOptions as $assertKey => $assertFieldName) {
                            if (array_key_exists($assertFieldName, $option)
                                && !(($assertFieldName == 'price' || $assertFieldName == 'sku')
                                    && in_array($option['type'], $this->specificTypes))
                            ) {
                                $expectedData[$expectedOptionId][$assertKey] = $option[$assertFieldName];
                            }
                        }
                    }
                }
                $optionValue = [];
                if (!empty($option['name']) && !empty($option['option_title'])) {
                    foreach ($this->_assertOptionValues as $assertKey => $assertFieldName) {
                        if (isset($option[$assertFieldName])) {
                            $optionValue[$assertKey] = $option[$assertFieldName];
                        }
                    }
                    $expectedValues[$expectedOptionId][] = $optionValue;
                }
            }
        }

        return [
            'id' => $expectedOptionId,
            'options' => $expectedOptions,
            'data' => $expectedData,
            'values' => $expectedValues,
        ];
    }

    /**
     * Updates expected options data array with existing unique options data
     *
     * @param array $expected
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options
     * @return array
     */
    protected function mergeWithExistingData(
        array $expected,
        $options
    ) {
        $expectedOptionId = $expected['id'];
        $expectedOptions = $expected['options'];
        $expectedData = $expected['data'];
        $expectedValues = $expected['values'];
        foreach ($options as $option) {
            $optionKey = $option->getType() . '|' . $option->getTitle();
            $optionValues = $this->getOptionValues($option);
            if (!in_array($optionKey, $expectedOptions)) {
                $expectedOptionId++;
                $expectedOptions[$expectedOptionId] = $optionKey;
                $expectedData[$expectedOptionId] = $this->getOptionData($option);
                if ($optionValues) {
                    $expectedValues[$expectedOptionId] = $optionValues;
                }
            } else {
                $existingOptionId = array_search($optionKey, $expectedOptions);
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $expectedData[$existingOptionId] = array_merge(
                    $this->getOptionData($option),
                    $expectedData[$existingOptionId]
                );
                if ($optionValues) {
                    foreach ($optionValues as $optionKey => $optionValue) {
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $expectedValues[$existingOptionId][$optionKey] = array_merge(
                            $optionValue,
                            $expectedValues[$existingOptionId][$optionKey]
                        );
                    }
                }
            }
        }

        return [
            'id' => $expectedOptionId,
            'options' => $expectedOptions,
            'data' => $expectedData,
            'values' => $expectedValues
        ];
    }

    /**
     *  Returns actual product data: current id, options, options data and option values
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options
     * @return array
     */
    protected function getActualOptionsData($options)
    {
        $actualOptionId = 0;
        $actualOptions = [];
        // array of type and title types, key is element ID
        $actualData = [];
        // array of option data
        $actualValues = [];
        // array of option values data
        /** @var $option \Magento\Catalog\Model\Product\Option */
        foreach ($options as $option) {
            $lastOptionKey = $option->getType() . '|' . $option->getTitle();
            $actualOptionId++;
            if (!in_array($lastOptionKey, $actualOptions)) {
                $actualOptions[$actualOptionId] = $lastOptionKey;
                $actualData[$actualOptionId] = $this->getOptionData($option);
                if ($optionValues = $this->getOptionValues($option)) {
                    $actualValues[$actualOptionId] = $optionValues;
                }
            }
        }
        return [
            'id' => $actualOptionId,
            'options' => $actualOptions,
            'data' => $actualData,
            'values' => $actualValues
        ];
    }

    /**
     * Retrieve option data
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array
     */
    protected function getOptionData(\Magento\Catalog\Model\Product\Option $option)
    {
        $result = [];
        foreach (array_keys($this->_assertOptions) as $assertKey) {
            $result[$assertKey] = $option->getData($assertKey);
        }
        return $result;
    }

    /**
     * Retrieve option values or false for options which has no values
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array|bool
     */
    protected function getOptionValues(\Magento\Catalog\Model\Product\Option $option)
    {
        $values = $option->getValues();
        if (!empty($values)) {
            $result = [];
            /** @var $value \Magento\Catalog\Model\Product\Option\Value */
            foreach ($values as $value) {
                $optionData = [];
                foreach (array_keys($this->_assertOptionValues) as $assertKey) {
                    if ($value->hasData($assertKey)) {
                        $optionData[$assertKey] = $value->getData($assertKey);
                    }
                }
                $result[] = $optionData;
            }
            return $result;
        }

        return false;
    }

    /**
     * @param array $expected
     * @return array
     */
    private function getFullExpectedOptions(array $expected): array
    {
        foreach ($expected as &$data) {
            foreach ($data as $store => &$options) {
                if ($store !== 'admin') {
                    foreach ($options as $optKey => &$option) {
                        $option += $data['admin'][$optKey];
                        if (isset($option['values'])) {
                            foreach ($option['values'] as $valKey => &$value) {
                                $value += $data['admin'][$optKey]['values'][$valKey];
                            }
                        }
                    }
                }
            }
        }
        return $expected;
    }

    /**
     * Tests import products with custom options.
     *
     * @dataProvider getCustomOptionDataProvider
     * @param string $importFile
     * @param string $sku1
     * @param string $sku2
     *
     * @return void
     */
    #[
        Config(CatalogConfig::XML_PATH_PRICE_SCOPE, CatalogConfig::PRICE_SCOPE_WEBSITE, ScopeInterface::SCOPE_STORE),
        DataFixture(StoreFixture::class, ['code' => 'secondstore']),
    ]
    public function testImportCustomOptions(string $importFile, string $sku1, string $sku2): void
    {
        $pathToFile = __DIR__ . '/../_files/' . $importFile;
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $importModel->importData();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(
            ProductRepositoryInterface::class
        );
        $product1 = $productRepository->get($sku1);

        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $product1);
        $options = $product1->getOptionInstance()->getProductOptions($product1);

        $expectedData = $this->getExpectedOptionsData($pathToFile);
        $expectedData = $this->mergeWithExistingData($expectedData, $options);
        $actualData = $this->getActualOptionsData($options);

        // assert of equal type+titles
        $expectedOptions = $expectedData['options'];
        // we need to save key values
        $actualOptions = $actualData['options'];
        sort($expectedOptions);
        sort($actualOptions);
        $this->assertSame($expectedOptions, $actualOptions);

        // assert of options data
        $this->assertCount(count($expectedData['data']), $actualData['data']);
        $this->assertCount(count($expectedData['values']), $actualData['values']);

        $this->productRepository->delete($product1);
        $product2 = $productRepository->get($sku2);
        $this->productRepository->delete($product2);
    }

    /**
     * @return array
     */
    public static function getCustomOptionDataProvider(): array
    {
        return [
            [
                'importFile' => 'multi_store_products_with_custom_options.csv',
                'sku1' => 'simple',
                'sku2' => 'simple2',
            ],
        ];
    }

    /**
     * Tests import product custom options with multiple uploads.
     *
     * @dataProvider getProductCustomOptionDataProvider
     * @param string $importFile
     * @param string $sku
     * @param int $uploadCount
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testImportProductCustomOptionsOnMultipleUploads(
        string $importFile,
        string $sku,
        int $uploadCount
    ): void {
        $pathToFile = __DIR__ . '/../_files/' . $importFile;

        for ($count = 0; $count < $uploadCount; $count++) {
            $productImportModel = $this->createImportModel($pathToFile);
            $errors = $productImportModel->validateData();
            $this->assertTrue($errors->getErrorsCount() == 0);
            $productImportModel->importData();
        }

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(
            ProductRepositoryInterface::class
        );
        $product = $productRepository->get($sku);

        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $product);
        $options = $product->getOptionInstance()->getProductOptions($product);

        $expectedData = $this->getExpectedOptionsData($pathToFile, 'default');
        $expectedOptions = $expectedData['options'];

        $this->assertCount(count($expectedOptions), $options);

        // Cleanup imported products
        try {
            $this->productRepository->delete($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
    }

    /**
     * @return array
     */
    public static function getProductCustomOptionDataProvider(): array
    {
        return [
            [
                'importFile' => 'product_with_custom_options_and_multiple_uploads.csv',
                'sku' => 'p1',
                'uploadCount' => 2,
            ],
        ];
    }
}
