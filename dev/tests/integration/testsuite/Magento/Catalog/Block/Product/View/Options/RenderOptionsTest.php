<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Options;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View\Options;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\Page;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Assert that product custom options render as expected.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class RenderOptionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $productCustomOptionFactory;

    /**
     * @var ProductCustomOptionValuesInterfaceFactory
     */
    private $productCustomOptionValuesFactory;

    /**
     * @var Page
     */
    private $page;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        CacheCleaner::cleanAll();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->productCustomOptionFactory = $this->objectManager->get(ProductCustomOptionInterfaceFactory::class);
        $this->productCustomOptionValuesFactory = $this->objectManager->get(
            ProductCustomOptionValuesInterfaceFactory::class
        );
        $this->page = $this->objectManager->create(Page::class);
        parent::setUp();
    }

    /**
     * Check that options from text group(field, area) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider customOptionsFromTextGroupDataProvider
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromTextGroup(array $optionData, array $checkArray): void
    {
        $option = $this->addOptionToProduct($optionData);
        $optionHtml = $this->getOptionHtml();
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);

        if ($optionData[Option::KEY_MAX_CHARACTERS] > 0) {
            $this->assertContains($checkArray['max_characters'], $optionHtml);
        } else {
            $this->assertNotContains('class="character-counter', $optionHtml);
        }
    }

    /**
     * Data provider with product custom options from text group(field, area).
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function customOptionsFromTextGroupDataProvider(): array
    {
        return [
            'type_field_required' => [
                [
                    Option::KEY_TITLE => 'Test option field title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-field-title-1',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 1</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<input type="text"',
                ]
            ],
            'type_field_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option field title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-field-title-2',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 2</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<input type="text"',
                ]
            ],
            'type_field_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option field title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-field-title-3',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 3</span>',
                    'price' => 'data-price-amount="50"',
                    'required_element' => '<input type="text"',
                ]
            ],
            'type_field_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option field title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'percent',
                    Option::KEY_SKU => 'test-option-field-title-4',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 4</span>',
                    'price' => 'data-price-amount="5"',
                    'required_element' => '<input type="text"',
                ]
            ],
            'type_field_max_characters' => [
                [
                    Option::KEY_TITLE => 'Test option field title 5',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-field-title-5',
                    Option::KEY_MAX_CHARACTERS => 99,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 5</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<input type="text"',
                    'max_characters' => 'Maximum 99 characters',
                ]
            ],
            'type_area_required' => [
                [
                    Option::KEY_TITLE => 'Test option area title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-area-title-1',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea required">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 1</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<textarea',
                ]
            ],
            'type_area_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option area title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-area-title-2',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 2</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<textarea',
                ]
            ],
            'type_area_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option area title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-area-title-3',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 3</span>',
                    'price' => 'data-price-amount="50"',
                    'required_element' => '<textarea',
                ]
            ],
            'type_area_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option area title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'percent',
                    Option::KEY_SKU => 'test-option-area-title-4',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 4</span>',
                    'price' => 'data-price-amount="5"',
                    'required_element' => '<textarea',
                ]
            ],
            'type_area_max_characters' => [
                [
                    Option::KEY_TITLE => 'Test option area title 5',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-area-title-5',
                    Option::KEY_MAX_CHARACTERS => 99,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 5</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<textarea',
                    'max_characters' => 'Maximum 99 characters',
                ]
            ],
        ];
    }

    /**
     * Check that options from file group(file) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider customOptionsFromFileGroupDataProvider
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromFileGroup(array $optionData, array $checkArray): void
    {
        $option = $this->addOptionToProduct($optionData);
        $optionHtml = $this->getOptionHtml();
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);
        $this->assertContains($checkArray['file_extension'], $optionHtml);

        if (isset($checkArray['file_width'])) {
            $checkArray['file_width'] = sprintf($checkArray['file_width'], __('Maximum image width'));
            $this->assertRegExp($checkArray['file_width'], $optionHtml);
        }

        if (isset($checkArray['file_height'])) {
            $checkArray['file_height'] = sprintf($checkArray['file_height'], __('Maximum image height'));
            $this->assertRegExp($checkArray['file_height'], $optionHtml);
        }
    }

    /**
     * Data provider with product custom options from file group(file).
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function customOptionsFromFileGroupDataProvider(): array
    {
        return [
            'type_file_required' => [
                [
                    Option::KEY_TITLE => 'Test option file title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-file-title-1',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file required">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 1</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<input type="file"',
                    'file_extension' => '<strong>png, jpg</strong>',
                ]
            ],
            'type_file_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option file title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-file-title-2',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 2</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '<input type="file"',
                    'file_extension' => '<strong>png, jpg</strong>',
                ]
            ],
            'type_file_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option file title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-file-title-3',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 3</span>',
                    'price' => 'data-price-amount="50"',
                    'required_element' => '<input type="file"',
                    'file_extension' => '<strong>png, jpg</strong>',
                ]
            ],
            'type_file_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option file title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'percent',
                    Option::KEY_SKU => 'test-option-file-title-4',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 4</span>',
                    'price' => 'data-price-amount="5"',
                    'required_element' => '<input type="file"',
                    'file_extension' => '<strong>png, jpg</strong>',
                ]
            ],
            'type_file_with_width_and_height' => [
                [
                    Option::KEY_TITLE => 'Test option file title 5',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'percent',
                    Option::KEY_SKU => 'test-option-file-title-5',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                    Option::KEY_IMAGE_SIZE_X => 10,
                    Option::KEY_IMAGE_SIZE_Y => 81,
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 5</span>',
                    'price' => 'data-price-amount="5"',
                    'required_element' => '<input type="file"',
                    'file_extension' => '<strong>png, jpg</strong>',
                    'file_width' => '/%s:.*<strong>10 px.<\/strong>/',
                    'file_height' => '/%s:.*<strong>81 px.<\/strong>/',
                ]
            ],
        ];
    }

    /**
     * Check that options from select group(drop-down, radio buttons, checkbox, multiple select) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider customOptionsFromSelectGroupDataProvider
     *
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
        $option = $this->addOptionToProduct($optionData, $optionValueData);
        $optionValues = $option->getValues();
        $optionValue = reset($optionValues);
        $optionHtml = $this->getOptionHtml();
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);

        if (isset($checkArray['not_contain_arr'])) {
            foreach ($checkArray['not_contain_arr'] as $notContainPattern) {
                $this->assertNotRegExp($notContainPattern, $optionHtml);
            }
        }

        if (isset($checkArray['option_value_item'])) {
            $checkArray['option_value_item'] = sprintf(
                $checkArray['option_value_item'],
                $optionValue->getOptionTypeId(),
                $optionValueData[Value::KEY_TITLE]
            );
            $this->assertRegExp($checkArray['option_value_item'], $optionHtml);
        }
    }

    /**
     * Data provider with product custom options from select
     * group(drop-down, radio buttons, checkbox, multiple select).
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function customOptionsFromSelectGroupDataProvider(): array
    {
        return [
            'type_drop_down_required' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-drop-down-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 1</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ]
            ],
            'type_drop_down_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-drop-down-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 2</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ]
            ],
            'type_drop_down_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-drop-down-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 3</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="50" >%s \+\s{11}\$50.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ]
            ],
            'type_drop_down_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'percent',
                    Value::KEY_SKU => 'test-option-drop-down-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 4</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="5" >%s \+\s{11}\$5.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ]
            ],

            'type_radio_button_required' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-radio-button-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 1</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_radio_button_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-radio-button-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 2</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_radio_button_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-radio-button-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 3</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="50"',
                ]
            ],
            'type_radio_button_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'percent',
                    Value::KEY_SKU => 'test-option-radio-button-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 4</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="5"',
                ]
            ],

            'type_checkbox_required' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-checkbox-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 1</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_checkbox_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-checkbox-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 2</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_checkbox_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-checkbox-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 3</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="50"',
                ]
            ],
            'type_checkbox_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'percent',
                    Value::KEY_SKU => 'test-option-checkbox-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 4</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="5"',
                ]
            ],

            'type_multiselect_required' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-multiselect-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 1</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                ]
            ],
            'type_multiselect_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-multiselect-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 2</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                ]
            ],
            'type_multiselect_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'fixed',
                    Value::KEY_SKU => 'test-option-multiselect-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 3</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="50" >%s \+\s{11}\$50.00.*/',
                ]
            ],
            'type_multiselect_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => 'percent',
                    Value::KEY_SKU => 'test-option-multiselect-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 4</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="5" >%s \+\s{11}\$5.00.*/',
                ]
            ],
        ];
    }

    /**
     * Check that options from date group(date, date & time, time) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider customOptionsFromDateGroupDataProvider
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromDateGroup(array $optionData, array $checkArray): void
    {
        $option = $this->addOptionToProduct($optionData);
        $optionHtml = $this->getOptionHtml();
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);

        switch ($optionData[Option::KEY_TYPE]) {
            case ProductCustomOptionInterface::OPTION_TYPE_DATE:
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][month]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][day]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][year]\"", $optionHtml);
                $this->assertNotContains("<select name=\"options[{$option->getOptionId()}][hour]\"", $optionHtml);
                $this->assertNotContains("<select name=\"options[{$option->getOptionId()}][minute]\"", $optionHtml);
                $this->assertNotContains("<select name=\"options[{$option->getOptionId()}][day_part]\"", $optionHtml);
                break;
            case ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME:
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][month]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][day]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][year]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][hour]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][minute]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][day_part]\"", $optionHtml);
                break;
            case ProductCustomOptionInterface::OPTION_TYPE_TIME:
                $this->assertNotContains("<select name=\"options[{$option->getOptionId()}][month]\"", $optionHtml);
                $this->assertNotContains("<select name=\"options[{$option->getOptionId()}][day]\"", $optionHtml);
                $this->assertNotContains("<select name=\"options[{$option->getOptionId()}][year]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][hour]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][minute]\"", $optionHtml);
                $this->assertContains("<select name=\"options[{$option->getOptionId()}][day_part]\"", $optionHtml);
                break;
        }
    }

    /**
     * Data provider with product custom options from date group(date, date & time, time).
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function customOptionsFromDateGroupDataProvider(): array
    {
        return [
            'type_date_required' => [
                [
                    Option::KEY_TITLE => 'Test option date title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-date-title-1',
                ],
                [
                    'block_with_required_class' => '<div class="field date required"',
                    'title' => '<span>Test option date title 1</span>',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_date_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option date title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-date-title-2',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date title 2</span>',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_date_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option date title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-date-title-3',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date title 3</span>',
                    'price' => 'data-price-amount="50"',
                ]
            ],
            'type_date_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option date title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'percent',
                    Option::KEY_SKU => 'test-option-date-title-4',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date title 4</span>',
                    'price' => 'data-price-amount="5"',
                ]
            ],
            'type_date_and_time_required' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-date-and-time-title-1',
                ],
                [
                    'block_with_required_class' => '<div class="field date required"',
                    'title' => '<span>Test option date and time title 1</span>',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_date_and_time_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-date-and-time-title-2',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date and time title 2</span>',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_date_and_time_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-date-and-time-title-3',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date and time title 3</span>',
                    'price' => 'data-price-amount="50"',
                ]
            ],
            'type_date_and_time_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'percent',
                    Option::KEY_SKU => 'test-option-date-and-time-title-4',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date and time title 4</span>',
                    'price' => 'data-price-amount="5"',
                ]
            ],
            'type_time_required' => [
                [
                    Option::KEY_TITLE => 'Test option time title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-time-title-1',
                ],
                [
                    'block_with_required_class' => '<div class="field date required"',
                    'title' => '<span>Test option time title 1</span>',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_time_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option time title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-time-title-2',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option time title 2</span>',
                    'price' => 'data-price-amount="10"',
                ]
            ],
            'type_time_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option time title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'fixed',
                    Option::KEY_SKU => 'test-option-time-title-3',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option time title 3</span>',
                    'price' => 'data-price-amount="50"',
                ]
            ],
            'type_time_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option time title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => 'percent',
                    Option::KEY_SKU => 'test-option-time-title-4',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option time title 4</span>',
                    'price' => 'data-price-amount="5"',
                ]
            ],
        ];
    }

    /**
     * Base asserts for rendered options.
     *
     * @param ProductCustomOptionInterface $option
     * @param string $optionHtml
     * @param array $checkArray
     * @return void
     */
    private function baseOptionAsserts(
        ProductCustomOptionInterface $option,
        string $optionHtml,
        array $checkArray
    ): void {
        $this->assertContains($checkArray['block_with_required_class'], $optionHtml);
        $this->assertContains($checkArray['title'], $optionHtml);

        if (isset($checkArray['label_for_created_option'])) {
            $checkArray['label_for_created_option'] = sprintf(
                $checkArray['label_for_created_option'],
                $option->getOptionId()
            );
            $this->assertContains($checkArray['label_for_created_option'], $optionHtml);
        }

        if (isset($checkArray['price'])) {
            $this->assertContains($checkArray['price'], $optionHtml);
        }
    }

    /**
     * Add custom option to product with data.
     *
     * @param array $optionData
     * @param array $optionValueData
     * @return ProductCustomOptionInterface
     */
    private function addOptionToProduct(array $optionData, array $optionValueData = []): ProductCustomOptionInterface
    {
        $product = $this->productRepository->get('simple');
        $optionData[Option::KEY_PRODUCT_SKU] = $product->getSku();

        if (!empty($optionValueData)) {
            $optionValueData = $this->productCustomOptionValuesFactory->create(['data' => $optionValueData]);
            $optionData['values'] = [$optionValueData];
        }

        $option = $this->productCustomOptionFactory->create(['data' => $optionData]);
        $product->setOptions([$option]);
        $this->productRepository->save($product);
        $product = $this->productRepository->get('simple');
        $createdOptions = $product->getOptions();

        return reset($createdOptions);
    }

    /**
     * Render custom options block.
     *
     * @return string
     */
    private function getOptionHtml(): string
    {
        $product = $this->productRepository->get('simple');
        $optionsBlock = $this->getOptionsBlock();
        $optionsBlock->setProduct($product);

        return $optionsBlock->toHtml();
    }

    /**
     * Get options block.
     *
     * @return Options
     */
    private function getOptionsBlock(): Options
    {
        $this->page->addHandle([
            'default',
            'catalog_product_view',
        ]);
        $this->page->getLayout()->generateXml();
        /** @var Template $productInfoFormOptionsBlock */
        $productInfoFormOptionsBlock = $this->page->getLayout()->getBlock('product.info.form.options');
        $optionsWrapperBlock = $productInfoFormOptionsBlock->getChildBlock('product_options_wrapper');

        return $optionsWrapperBlock->getChildBlock('product_options');
    }
}
