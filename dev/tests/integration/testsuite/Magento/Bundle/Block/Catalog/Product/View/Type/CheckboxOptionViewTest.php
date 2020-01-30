<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

/**
 * Class checks checkbox bundle options appearance
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CheckboxOptionViewTest extends AbstractBundleOptionsViewTest
{
    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_checkbox_options.php
     *
     * @return void
     */
    public function testNotRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView('bundle-product', 'Checkbox Options', $expectedSelectionsNames);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_checkbox_required_options.php
     *
     * @return void
     */
    public function testRequiredSelectMultiSelectionsView(): void
    {
        $expectedSelectionsNames = ['Simple Product', 'Simple Product2'];
        $this->processMultiSelectionsView('bundle-product', 'Checkbox Options', $expectedSelectionsNames, true);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_checkbox_required_option.php
     *
     * @return void
     */
    public function testShowSingle(): void
    {
        $this->processSingleSelectionView('bundle-product', 'Checkbox Options');
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredSelectXpath(): string
    {
        return "//div[contains(@class, 'choice') and //input[@type='checkbox'"
            . "and contains(@data-validate, 'validate-one-required-by-name')] and label//span[text() = '1 x %s']]";
    }

    /**
     * @inheritdoc
     */
    protected function getNotRequiredSelectXpath(): string
    {
        return  "//div[contains(@class, 'choice') and //input[@type='checkbox'"
            . "and not(contains(@data-validate, 'validate-one-required-by-name'))] and label//span[text() = '1 x %s']]";
    }
}
