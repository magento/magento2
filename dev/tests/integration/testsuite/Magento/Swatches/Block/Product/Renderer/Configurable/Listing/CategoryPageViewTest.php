<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product\Renderer\Configurable\Listing;

use Magento\Swatches\Block\Product\Renderer\Configurable\ProductPageViewTest;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable;

/**
 * Test class to check configurable product with swatch attributes view behaviour on category page
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CategoryPageViewTest extends ProductPageViewTest
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->block = $this->layout->createBlock(Configurable::class);
        $this->template = 'Magento_Swatches::product/listing/renderer.phtml';
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_visual_swatch_attribute.php
     *
     * @dataProvider expectedVisualSwatchDataProvider
     *
     * @param array $expectedConfig
     * @param array $expectedSwatchConfig
     * @return void
     */
    public function testCategoryPageVisualSwatchAttributeView(array $expectedConfig, array $expectedSwatchConfig): void
    {
        $this->checkProductViewCategoryPage($expectedConfig, $expectedSwatchConfig, ['visual_swatch_attribute']);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_text_swatch_attribute.php
     *
     * @dataProvider expectedTextSwatchDataProvider
     *
     * @param array $expectedConfig
     * @param array $expectedSwatchConfig
     * @return void
     */
    public function testCategoryPageTextSwatchAttributeView(array $expectedConfig, array $expectedSwatchConfig): void
    {
        $this->checkProductViewCategoryPage($expectedConfig, $expectedSwatchConfig, ['text_swatch_attribute']);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_two_attributes.php
     *
     * @dataProvider expectedTwoAttributesProvider
     *
     * @param array $expectedConfig
     * @param array $expectedSwatchConfig
     * @return void
     */
    public function testCategoryPageTwoAttributesView(array $expectedConfig, array $expectedSwatchConfig): void
    {
        $this->checkProductViewCategoryPage(
            $expectedConfig,
            $expectedSwatchConfig,
            ['visual_swatch_attribute', 'text_swatch_attribute']
        );
    }

    /**
     * Check configurable product view on category view page
     *
     * @param array $expectedConfig
     * @param array $expectedSwatchConfig
     * @param array $attributes
     * @return void
     */
    private function checkProductViewCategoryPage(
        array $expectedConfig,
        array $expectedSwatchConfig,
        array $attributes
    ): void {
        $this->setAttributeUsedInProductListing($attributes);
        $this->checkProductView($expectedConfig, $expectedSwatchConfig);
    }

    /**
     * Set used in product listing attributes value to true
     *
     * @param array $attributeCodes
     * @return void
     */
    private function setAttributeUsedInProductListing(array $attributeCodes): void
    {
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->productAttributeRepository->get($attributeCode);
            $attribute->setUsedInProductListing('1');
            $this->productAttributeRepository->save($attribute);
        }
    }
}
