<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

/**
 * Class checks dropdown bundle options appearance
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class DropDownOptionViewTest extends AbstractBundleOptionsViewTest
{
    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_dropdown_options.php
     *
     * @return void
     */
    public function testNotRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView(
            'bundle-product-dropdown-options',
            'Dropdown Options',
            $expectedSelectionsNames
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_dropdown_required_options.php
     *
     * @return void
     */
    public function testRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView(
            'bundle-product-dropdown-required-options',
            'Dropdown Options',
            $expectedSelectionsNames,
            true
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     *
     * @return void
     */
    public function testShowSingle(): void
    {
        $this->processSingleSelectionView('bundle-product', 'Bundle Product Items');
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredSelectXpath(): string
    {
        return "//select[contains(@id, 'bundle-option') and contains(@data-validate, 'required:true')]"
            . "/option/span[normalize-space(text()) = '%s']";
    }

    /**
     * @inheritdoc
     */
    protected function getNotRequiredSelectXpath(): string
    {
        return "//select[contains(@id, 'bundle-option') and not(contains(@data-validate, 'required:true'))]"
            . "/option/span[normalize-space(text()) = '%s']";
    }
}
