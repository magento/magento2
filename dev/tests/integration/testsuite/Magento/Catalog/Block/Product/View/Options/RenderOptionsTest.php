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
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\TextGroupDataProvider::getData()
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
     * Check that options from file group(file) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\FileGroupDataProvider::getData()
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
     * Check that options from select group(drop-down, radio buttons, checkbox, multiple select) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\SelectGroupDataProvider::getData()
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
     * Check that options from date group(date, date & time, time) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\DateGroupDataProvider::getData()
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
