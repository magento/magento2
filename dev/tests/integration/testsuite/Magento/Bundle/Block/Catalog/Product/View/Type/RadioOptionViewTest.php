<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

/**
 * Class checks radio buttons bundle options appearance
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class RadioOptionViewTest extends AbstractBundleOptionsViewTest
{
    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_radio_options.php
     *
     * @return void
     */
    public function testNotRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView('bundle-product-radio-options', 'Radio Options', $expectedSelectionsNames);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_radio_required_options.php
     *
     * @return void
     */
    public function testRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView(
            'bundle-product-radio-required-options',
            'Radio Options',
            $expectedSelectionsNames,
            true
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_radio_required_option.php
     *
     * @return void
     */
    public function testShowSingle(): void
    {
        $this->processSingleSelectionView('bundle-product-radio-required-option', 'Radio Options');
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredSelectXpath(): string
    {
        return "//input[@type='radio' and contains(@data-validate, 'validate-one-required-by-name')"
            . "and contains(@class, 'bundle option')]/../label//span[normalize-space(text()) = '%s']";
    }

    /**
     * @inheritdoc
     */
    protected function getNotRequiredSelectXpath(): string
    {
        return "//input[@type='radio' and not(contains(@data-validate, 'validate-one-required-by-name'))"
            . "and contains(@class, 'bundle option')]/../label//span[normalize-space(text()) = '%s']";
    }
}
