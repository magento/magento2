<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Asserts what custom option values are same as expected.
 */
class AssertCustomOptions extends AssertProductForm
{
    /**
     * Assert form data equals fixture data.
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        FixtureInterface $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage
    ) {
        $expectedCustomOptions = $this->arguments['expectedCustomOptions'];
        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filter);
        $productData = [];
        if ($product->hasData('custom_options')) {
            $productData = $this->addExpectedOptionValues($product, $expectedCustomOptions);
        }
        $fixtureData = $this->prepareFixtureData($productData, $this->sortFields);
        $formData = $this->prepareFormData($productPage->getProductForm()->getData($product), $this->sortFields);
        $errors = $this->verifyData($fixtureData, $formData);

        \PHPUnit\Framework\Assert::assertEmpty($errors, $errors);
    }

    /**
     * Adds expected value of Custom Options.
     *
     * @param FixtureInterface $product
     * @param array $expectedCustomOptions
     * @return array
     */
    private function addExpectedOptionValues(FixtureInterface $product, array $expectedCustomOptions)
    {
        /** @var array $customOptionsSource */
        $customOptionsSource = $product->getDataFieldConfig('custom_options')['source']->getCustomOptions();
        foreach (array_keys($customOptionsSource) as $optionKey) {
            foreach ($expectedCustomOptions as $expectedCustomOption) {
                if ($customOptionsSource[$optionKey]['type'] === $expectedCustomOption['optionType']) {
                    $options = array_keys($customOptionsSource[$optionKey]['options']);
                    $optionField = $expectedCustomOption['optionField'];
                    $optionValue = $expectedCustomOption['optionValue'];
                    foreach ($options as $optionsKey) {
                        $customOptionsSource[$optionKey]['options'][$optionsKey][$optionField] = $optionValue;
                    }
                }
            }
        }

        return ['custom_options' => $customOptionsSource];
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom option values are same as expected.';
    }
}
