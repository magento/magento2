<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

/**
 * Class checks multiselect bundle options appearance
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class MultiselectOptionViewTest extends AbstractBundleOptionsViewTest
{
    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_multiselect_options.php
     *
     * @return void
     */
    public function testNotRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView(
            'bundle-product-multiselect-options',
            'Multiselect Options',
            $expectedSelectionsNames
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_multiselect_required_options.php
     *
     * @return void
     */
    public function testRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView(
            'bundle-product-multiselect-required-options',
            'Multiselect Options',
            $expectedSelectionsNames,
            true
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_multiselect_required_option.php
     *
     * @return void
     */
    public function testShowSingle(): void
    {
        $this->processSingleSelectionView('bundle-product-multiselect-required-option', 'Multiselect Options');
    }

    /**
     * @inheridoc
     */
    protected function getRequiredSelectXpath(): string
    {
        return "//select[contains(@id, 'bundle-option') and @multiple='multiple' "
            . "and contains(@data-validate, 'required:true')]/option/span[normalize-space(text()) = '1 x %s']";
    }

    /**
     * @inheridoc
     */
    protected function getNotRequiredSelectXpath(): string
    {
        return "//select[contains(@id, 'bundle-option') and @multiple='multiple'"
            . "and not(contains(@data-validate, 'required:true'))]/option/span[normalize-space(text()) = '1 x %s']";
    }
}
