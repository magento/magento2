<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\CustomOptions;

use Magento\Catalog\Block\Product\View\Options\AbstractRenderCustomOptionsTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Test cases related to check that configurable product custom option renders as expected.
 *
 * @magentoAppArea frontend
 */
class RenderOptionsTest extends AbstractRenderCustomOptionsTest
{
    /**
     * Check that options from text group(field, area) render on configurable product as expected.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\ConfigurableProduct\Block\CustomOptions\TextGroupDataProvider::class, 'getData')]
    public function testRenderCustomOptionsFromTextGroup(array $optionData, array $checkArray): void
    {
        $this->assertTextOptionRenderingOnProduct('Configurable product', $optionData, $checkArray);
    }

    /**
     * Check that options from file group(file) render on configurable product as expected.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\ConfigurableProduct\Block\CustomOptions\FileGroupDataProvider::class, 'getData')]
    public function testRenderCustomOptionsFromFileGroup(array $optionData, array $checkArray): void
    {
        $this->assertFileOptionRenderingOnProduct('Configurable product', $optionData, $checkArray);
    }

    /**
     * Check that options from select group(drop-down, radio buttons, checkbox, multiple select) render
     * on configurable product as expected.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\ConfigurableProduct\Block\CustomOptions\SelectGroupDataProvider::class, 'getData')]
    public function testRenderCustomOptionsFromSelectGroup(
        array $optionData,
        array $optionValueData,
        array $checkArray
    ): void {
        $this->assertSelectOptionRenderingOnProduct('Configurable product', $optionData, $optionValueData, $checkArray);
    }

    /**
     * Check that options from date group(date, date & time, time) render on configurable product as expected.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\ConfigurableProduct\Block\CustomOptions\DateGroupDataProvider::class, 'getData')]
    public function testRenderCustomOptionsFromDateGroup(array $optionData, array $checkArray): void
    {
        $this->assertDateOptionRenderingOnProduct('Configurable product', $optionData, $checkArray);
    }

    /**
     * @inheritdoc
     */
    protected function getHandlesList(): array
    {
        return [
            'default',
            'catalog_product_view',
            'catalog_product_view_type_configurable',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getMaxCharactersCssClass(): string
    {
        return 'class="character-counter';
    }

    /**
     * @inheritdoc
     */
    protected function getOptionsBlockName(): string
    {
        return 'product.info.options';
    }
}
