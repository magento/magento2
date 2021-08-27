<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\Store\Model\StoreManagerInterface;

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
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
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
        $this->createImportModel($pathToFile)->importData();
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
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     */
    public function testSaveCustomOptionsWithMultipleStoreViews()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $storeCodes = [
            'admin',
            'default',
            'secondstore',
        ];
        /** @var StoreManagerInterface $storeManager */
        $importFile = 'product_with_custom_options_and_multiple_store_views.csv';
        $sku = 'simple';
        $pathToFile = __DIR__ . '/../_files/' . $importFile;
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0, 'Import File Validation Failed');
        $importModel->importData();
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        foreach ($storeCodes as $storeCode) {
            $storeManager->setCurrentStore($storeCode);
            $product = $productRepository->get($sku);
            $options = $product->getOptionInstance()->getProductOptions($product);
            $expectedData = $this->getExpectedOptionsData($pathToFile, $storeCode);
            $expectedData = $this->mergeWithExistingData($expectedData, $options);
            $actualData = $this->getActualOptionsData($options);
            // assert of equal type+titles
            $expectedOptions = $expectedData['options'];
            // we need to save key values
            $actualOptions = $actualData['options'];
            sort($expectedOptions);
            sort($actualOptions);
            $this->assertEquals(
                $expectedOptions,
                $actualOptions,
                'Expected and actual options arrays does not match'
            );

            // assert of options data
            $this->assertCount(
                count($expectedData['data']),
                $actualData['data'],
                'Expected and actual data count does not match'
            );
            $this->assertCount(
                count($expectedData['values']),
                $actualData['values'],
                'Expected and actual values count does not match'
            );

            foreach ($expectedData['options'] as $expectedId => $expectedOption) {
                $elementExist = false;
                // find value in actual options and values
                foreach ($actualData['options'] as $actualId => $actualOption) {
                    if ($actualOption == $expectedOption) {
                        $elementExist = true;
                        $this->assertEquals(
                            $expectedData['data'][$expectedId],
                            $actualData['data'][$actualId],
                            'Expected data does not match actual data'
                        );
                        if (array_key_exists($expectedId, $expectedData['values'])) {
                            $this->assertEquals(
                                $expectedData['values'][$expectedId],
                                $actualData['values'][$actualId],
                                'Expected values does not match actual data'
                            );
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
            $this->createImportModel($pathToFile)->importData();
            $this->assertEquals(
                $customOptionValues,
                $this->getCustomOptionValues($sku),
                'Option IDs changed after second import'
            );
        }
    }

    /**
     * @return array
     */
    public function getBehaviorDataProvider(): array
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
}
