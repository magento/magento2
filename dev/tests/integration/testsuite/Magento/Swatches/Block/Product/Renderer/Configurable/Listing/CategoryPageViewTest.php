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
        $this->setAttributeUsedInProductListing('visual_swatch_attribute');
        $product = $this->productRepository->get('configurable');
        $this->block->setProduct($product);
        $result = $this->generateBlockData();
        $this->assertConfig($result['json_config'], $expectedConfig);
        $this->assertSwatchConfig($result['json_swatch_config'], $expectedSwatchConfig);
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
        $this->setAttributeUsedInProductListing('visual_swatch_attribute');
        $product = $this->productRepository->get('configurable');
        $this->block->setProduct($product);
        $result = $this->generateBlockData();
        $this->assertConfig($result['json_config'], $expectedConfig);
        $this->assertSwatchConfig($result['json_swatch_config'], $expectedSwatchConfig);
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
        $this->setAttributeUsedInProductListing('visual_swatch_attribute');
        $this->setAttributeUsedInProductListing('text_swatch_attribute');
        $product = $this->productRepository->get('configurable');
        $this->block->setProduct($product);
        $result = $this->generateBlockData();
        $this->assertConfig($result['json_config'], $expectedConfig);
        $this->assertSwatchConfig($result['json_swatch_config'], $expectedSwatchConfig);
    }

    /**
     * Set used in product listing attribute value to true
     *
     * @param string $attributeCode
     * @return void
     */
    private function setAttributeUsedInProductListing(string $attributeCode)
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setUsedInProductListing('1');
        $this->attributeRepository->save($attribute);
    }
}
