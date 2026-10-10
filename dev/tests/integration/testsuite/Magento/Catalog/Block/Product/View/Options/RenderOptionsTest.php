<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Options;

use Magento\TestFramework\Catalog\Block\Product\View\Options\DateGroupDataProvider;
use Magento\TestFramework\Catalog\Block\Product\View\Options\FileGroupDataProvider;
use Magento\TestFramework\Catalog\Block\Product\View\Options\SelectGroupDataProvider;
use Magento\TestFramework\Catalog\Block\Product\View\Options\TextGroupDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Test cases related to check that simple product custom option renders as expected.
 *
 * @magentoAppArea frontend
 */
class RenderOptionsTest extends AbstractRenderCustomOptionsTest
{
    /**
     * Check that options from text group (field, area) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(TextGroupDataProvider::class, 'getData')]
    public function testRenderCustomOptionsFromTextGroup(array $optionData, array $checkArray): void
    {
        $this->assertTextOptionRenderingOnProduct('simple', $optionData, $checkArray);
    }

    /**
     * Check that options from file group (file) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(FileGroupDataProvider::class, 'getData')]
    public function testRenderCustomOptionsFromFileGroup(array $optionData, array $checkArray): void
    {
        $this->assertFileOptionRenderingOnProduct('simple', $optionData, $checkArray);
    }

    /**
     * Check that options from select group (drop-down, radio buttons, checkbox, multiple select)
     * render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(SelectGroupDataProvider::class, 'getData')]
    public function testRenderCustomOptionsFromSelectGroup(
        array $optionData,
        array $optionValueData,
        array $checkArray
    ): void {
        $this->assertSelectOptionRenderingOnProduct('simple', $optionData, $optionValueData, $checkArray);
    }

    /**
     * Check that options from date group (date, date & time, time) render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     *
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(DateGroupDataProvider::class, 'getData')]
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
