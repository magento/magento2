<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Options;

/**
 * Test cases related to check that simple product custom option renders as expected.
 *
 * @magentoAppArea frontend
 */
class RenderOptionsTest extends AbstractRenderCustomOptionsTest
{
    /**
     * Check that options from text group(field, area) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\TextGroupDataProvider::getData
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromTextGroup(array $optionData, array $checkArray): void
    {
        $this->assertTextOptionRenderingOnProduct('simple', $optionData, $checkArray);
    }

    /**
     * Check that options from file group(file) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\FileGroupDataProvider::getData
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromFileGroup(array $optionData, array $checkArray): void
    {
        $this->assertFileOptionRenderingOnProduct('simple', $optionData, $checkArray);
    }

    /**
     * Check that options from select group(drop-down, radio buttons, checkbox, multiple select) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\SelectGroupDataProvider::getData
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
        $this->assertSelectOptionRenderingOnProduct('simple', $optionData, $optionValueData, $checkArray);
    }

    /**
     * Check that options from date group(date, date & time, time) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @dataProvider \Magento\TestFramework\Catalog\Block\Product\View\Options\DateGroupDataProvider::getData
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    public function testRenderCustomOptionsFromDateGroup(array $optionData, array $checkArray): void
    {
        $this->assertDateOptionRenderingOnProduct('simple', $optionData, $checkArray);
    }

    /**
     * @inheritdoc
     */
    protected function getHandlesList(): array
    {
        return [
            'default',
            'catalog_product_view',
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
