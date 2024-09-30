<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\View\Options\AbstractRenderCustomOptionsTest;
use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\TestFramework\Helper\Xpath;

/**
 * Test cases related to check that simple product custom option renders as expected.
 *
 * @magentoAppArea adminhtml
 */
class OptionsTest extends AbstractRenderCustomOptionsTest
{
    /** @var HelperProduct */
    private $helperProduct;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->helperProduct = $this->objectManager->get(HelperProduct::class);
        $this->dataObjectFactory = $this->objectManager->get(DataObjectFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @return void
     */
    public function testRenderCustomOptionsWithoutOptions(): void
    {
        $product = $this->productRepository->get('simple');
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                "//fieldset[@id='product_composite_configure_fields_options']",
                $this->getOptionHtml($product)
            ),
            'The option block is expected to be empty!'
        );
    }

    /**
     * Check that options from text group(field, area) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider renderCustomOptionsFromTextGroupProvider
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromTextGroup(array $optionData, array $checkArray): void
    {
        $this->assertTextOptionRenderingOnProduct('simple', $optionData, $checkArray);
    }

    /**
     * Provides test data to verify the display of text type options.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function renderCustomOptionsFromTextGroupProvider(): array
    {
        return [
            'type_text_required_field' => [
                [
                    Option::KEY_TITLE => 'Test option type text 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 0,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'contains' => [
                        'block_with_required_class' => '<div class="field admin__field">',
                        'title' => 'Test option type text 1',
                    ],
                    'equals_xpath' => [
                        'zero_price' => [
                            'xpath' => "//label[contains(@class, 'admin__field-label')]/span",
                            'message' => 'Expected empty price is incorrect or missing!',
                            'expected' => 0,
                        ],
                    ],
                ],
            ],
            'type_text_is_required_option' => [
                [
                    Option::KEY_TITLE => 'Test option type text 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 0,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'contains' => [
                        'block_with_required_class' => '<div class="field admin__field required _required">',
                    ],
                ],
            ],
            'type_text_fixed_positive_price' => [
                [
                    Option::KEY_TITLE => 'Test option type text 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'contains' => [
                        'price' => 'data-price-amount="50"',
                    ],
                    'equals_xpath' => [
                        'sign_price' => [
                            'xpath' => "//label[contains(@class, 'admin__field-label')]/span[contains(text(), '+')]",
                            'message' => 'Expected positive price is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_text_fixed_negative_price' => [
                [
                    Option::KEY_TITLE => 'Test option type text 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => -50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'contains' => [
                        'price' => 'data-price-amount="50"',
                    ],
                    'equals_xpath' => [
                        'sign_price' => [
                            'xpath' => "//label[contains(@class, 'admin__field-label')]/span[contains(text(), '-')]",
                            'message' => 'Expected negative price is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_text_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option type text 5',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'contains' => [
                        'price' => 'data-price-amount="5"',
                    ],
                ],
            ],
            'type_text_max_characters' => [
                [
                    Option::KEY_TITLE => 'Test option type text 6',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_MAX_CHARACTERS => 99,
                ],
                [
                    'max_characters' => (string)__('Maximum number of characters:') . ' <strong>99</strong>',
                ],
            ],
            'type_field' => [
                [
                    Option::KEY_TITLE => 'Test option type field 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_MAX_CHARACTERS => 0,
                    'configure_option_value' => 'Type field option value',
                ],
                [
                    'equals_xpath' => [
                        'control_price_attribute' => [
                            'xpath' => "//input[@id='options_%s_text' and @price='%s']",
                            'message' => 'Expected input price is incorrect or missing!',
                        ],
                        'default_option_value' => [
                            'xpath' => "//input[@id='options_%s_text' and @value='Type field option value']",
                            'message' => 'Expected input default value is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_area' => [
                [
                    Option::KEY_TITLE => 'Test option type area 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_MAX_CHARACTERS => 0,
                    'configure_option_value' => 'Type area option value',
                ],
                [
                    'equals_xpath' => [
                        'control_price_attribute' => [
                            'xpath' => "//textarea[@id='options_%s_text' and @price='%s']",
                            'message' => 'Expected textarea price is incorrect or missing!',
                        ],
                        'default_option_value' => [
                            'xpath' => "//textarea[@id='options_%s_text' "
                                . "and contains(text(), 'Type area option value')]",
                            'message' => 'Expected textarea default value is incorrect or missing!',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Check that options from select group(drop-down, radio buttons, checkbox, multiple select) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider renderCustomOptionsFromSelectGroupProvider
     * @param array $optionData
     * @param array $optionValueData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromSelectGroup(
        array $optionData,
        array $optionValueData,
        array $checkArray
    ): void {
        $this->assertSelectOptionRenderingOnProduct('simple', $optionData, $optionValueData, $checkArray);
    }

    /**
     * Provides test data to verify the display of select type options.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function renderCustomOptionsFromSelectGroupProvider(): array
    {
        return [
            'type_select_required_field' => [
                [
                    Option::KEY_TITLE => 'Test option type select 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Select value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                ],
                [
                    'contains' => [
                        'block_with_required_class' => '<div class="admin__field field">',
                        'title' => '<span>Test option type select 1</span>',
                    ],
                    'equals_xpath' => [
                        'required_element' => [
                            'xpath' => "//select[@id='select_%s']",
                            'message' => 'Expected select type is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_select_is_required_option' => [
                [
                    Option::KEY_TITLE => 'Test option type select 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Select value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                ],
                [
                    'contains' => [
                        'block_with_required_class' => '<div class="admin__field field _required">',
                    ],
                ],
            ],
            'type_drop_down_with_selected' => [
                [
                    Option::KEY_TITLE => 'Test option type drop-down 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                    'configure_option_value' => 'Drop-down value 1',
                ],
                [
                    Value::KEY_TITLE => 'Drop-down value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                ],
                [
                    'equals_xpath' => [
                        'element_type' => [
                            'xpath' => "//select[contains(@class, 'admin__control-select')]",
                            'message' => 'Expected drop down type is incorrect or missing!',
                        ],
                        'default_value' => [
                            'xpath' => "//option[contains(text(), '" . __('-- Please Select --') . "')]",
                            'message' => 'Expected default value is incorrect or missing!',
                        ],
                        'selected_value' => [
                            'xpath' => "//option[@selected='selected' and contains(text(), 'Drop-down value 1')]",
                            'message' => 'Expected selected value is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_multiple_with_selected' => [
                [
                    Option::KEY_TITLE => 'Test option type multiple 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 0,
                    'configure_option_value' => 'Multiple value 1',
                ],
                [
                    Value::KEY_TITLE => 'Multiple value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                ],
                [
                    'equals_xpath' => [
                        'element_type' => [
                            'xpath' => "//select[contains(@class, 'admin__control-multiselect') "
                                . "and @multiple='multiple']",
                            'message' => 'Expected multiple type is incorrect or missing!',
                        ],
                        'selected_value' => [
                            'xpath' => "//option[@selected='selected' and contains(text(), 'Multiple value 1')]",
                            'message' => 'Expected selected value is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_checkable_required_field' => [
                [
                    Option::KEY_TITLE => 'Test option type checkable 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Checkable value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                ],
                [
                    'equals_xpath' => [
                        'required_checkable_option' => [
                            'xpath' => "//div[@id='options-%s-list']",
                            'message' => 'Expected checkable option is incorrect or missing!',
                        ],
                        'option_value_title' => [
                            'xpath' => "//label[@for='options_%s_2']/span[contains(text(), 'Checkable value 1')]",
                            'message' => 'Expected option value title is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_radio_is_required_option' => [
                [
                    Option::KEY_TITLE => 'Test option type radio 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Radio value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                ],
                [
                    'equals_xpath' => [
                        'span_container' => [
                            'xpath' => "//span[@id='options-%s-container']",
                            'message' => 'Expected span container is incorrect or missing!',
                        ],
                        'default_option_value' => [
                            'xpath' => "//label[@for='options_%s']/span[contains(text(), '" . __('None') . "')]",
                            'message' => 'Expected default option value is incorrect or missing!',
                            'expected' => 0,
                        ],
                    ],
                ],
            ],
            'type_radio_with_selected' => [
                [
                    Option::KEY_TITLE => 'Test option type radio 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                    'configure_option_value' => 'Radio value 1',
                ],
                [
                    Value::KEY_TITLE => 'Radio value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                ],
                [
                    'equals_xpath' => [
                        'default_option_value' => [
                            'xpath' => "//label[@for='options_%s']/span[contains(text(), '" . __('None') . "')]",
                            'message' => 'Expected default option value is incorrect or missing!',
                        ],
                        'element_type' => [
                            'xpath' => "//input[@id='options_%s_2' and contains(@class, 'admin__control-radio')]",
                            'message' => 'Expected radio type is incorrect or missing!',
                        ],
                        'selected_value' => [
                            'xpath' => "//input[@id='options_%s_2' and @checked='checked']",
                            'message' => 'Expected selected option value is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_checkbox_is_required_option' => [
                [
                    Option::KEY_TITLE => 'Test option type checkbox 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Checkbox value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => '',
                ],
                [
                    'equals_xpath' => [
                        'span_container' => [
                            'xpath' => "//span[@id='options-%s-container']",
                            'message' => 'Expected span container is incorrect or missing!',
                        ],
                    ],
                ],
            ],
            'type_checkbox_with_selected' => [
                [
                    Option::KEY_TITLE => 'Test option type checkbox 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 0,
                    'configure_option_value' => 'Checkbox value 1',
                ],
                [
                    Value::KEY_TITLE => 'Checkbox value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => '',
                ],
                [
                    'equals_xpath' => [
                        'element_type' => [
                            'xpath' => "//input[@id='options_%s_2' and contains(@class, 'admin__control-checkbox')]",
                            'message' => 'Expected checkbox type is incorrect or missing!',
                        ],
                        'selected_value' => [
                            'xpath' => "//input[@id='options_%s_2' and @checked='checked']",
                            'message' => 'Expected selected option value is incorrect or missing!',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function addOptionToProduct(
        ProductInterface $product,
        array $optionData,
        array $optionValueData = []
    ): ProductInterface {
        $product = parent::addOptionToProduct($product, $optionData, $optionValueData);

        if (isset($optionData['configure_option_value'])) {
            $optionValue = $optionData['configure_option_value'];
            $option = $this->findOptionByTitle($product, $optionData[Option::KEY_TITLE]);
            if (!empty($optionValueData)) {
                $optionValueObject = $this->findOptionValueByTitle($option, $optionValue);
                $optionValue = $option->getType() === Option::OPTION_TYPE_CHECKBOX
                    ? [$optionValueObject->getOptionTypeId()]
                    : $optionValueObject->getOptionTypeId();
            }
            /** @var DataObject $request */
            $buyRequest = $this->dataObjectFactory->create();
            $buyRequest->setData([
                'qty' => 1,
                'options' => [$option->getId() => $optionValue],
            ]);
            $this->helperProduct->prepareProductOptions($product, $buyRequest);
        }

        return $product;
    }

    /**
     * @inheritdoc
     */
    protected function baseOptionAsserts(
        ProductCustomOptionInterface $option,
        string $optionHtml,
        array $checkArray
    ): void {
        if (isset($checkArray['contains'])) {
            foreach ($checkArray['contains'] as $needle) {
                $this->assertStringContainsString($needle, $optionHtml);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function additionalTypeTextAsserts(
        ProductCustomOptionInterface $option,
        string $optionHtml,
        array $checkArray
    ): void {
        parent::additionalTypeTextAsserts($option, $optionHtml, $checkArray);

        if (isset($checkArray['equals_xpath'])) {
            foreach ($checkArray['equals_xpath'] as $key => $value) {
                $value['args'] = $key === 'control_price_attribute' ? [(float)$option->getPrice()] : [];
                $this->assertEqualsXpath($option, $optionHtml, $value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function additionalTypeSelectAsserts(
        ProductCustomOptionInterface $option,
        string $optionHtml,
        array $checkArray
    ): void {
        parent::additionalTypeSelectAsserts($option, $optionHtml, $checkArray);

        if (isset($checkArray['equals_xpath'])) {
            foreach ($checkArray['equals_xpath'] as $value) {
                $this->assertEqualsXpath($option, $optionHtml, $value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getHandlesList(): array
    {
        return [
            'default',
            'CATALOG_PRODUCT_COMPOSITE_CONFIGURE',
            'catalog_product_view_type_simple',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getMaxCharactersCssClass(): string
    {
        return 'class="note"';
    }

    /**
     * @inheritdoc
     */
    protected function getOptionsBlockName(): string
    {
        return 'product.composite.fieldset.options';
    }

    /**
     * Checks that the xpath string is equal to the expected value
     *
     * @param ProductCustomOptionInterface $option
     * @param string $html
     * @param array $xpathData
     * @return void
     */
    private function assertEqualsXpath(ProductCustomOptionInterface $option, string $html, array $xpathData): void
    {
        $args = array_merge([$option->getOptionId()], $xpathData['args'] ?? []);
        $expected = $xpathData['expected'] ?? 1;
        $this->assertEquals(
            $expected,
            Xpath::getElementsCountForXpath(sprintf($xpathData['xpath'], ...$args), $html),
            $xpathData['message']
        );
    }
}
