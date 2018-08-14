<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that symbol before price is correct.
 */
class AssertAddBeforeForPrice extends AbstractConstraint
{
    /**
     * Assert that symbol before price is correct.
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @param string $priceTypeSymbol
     * @param \Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew $catalogProductNew
     */
    public function processAssert(
        FixtureInterface $product,
        CatalogProductIndex $productGrid,
        string $priceTypeSymbol,
        \Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew $catalogProductNew
    ) {
        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filter);

        $catalogProductNew->getProductForm()->openSection('customer-options');

        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options $options */
        $options = $catalogProductNew->getProductForm()->getSection('customer-options');
        $customOptions = $product->getCustomOptions()['import']['options'];

        foreach ($customOptions as $customOption) {
            /** @var array $valuesFromForm */
            $valuesFromForm = $options->getValuesDataForOption(
                $customOption['options'],
                $customOption['type'],
                $customOption['title']
            );

            foreach ($valuesFromForm as $value) {
                \PHPUnit_Framework_Assert::assertEquals($priceTypeSymbol, $value['add_before']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Price for custom options has correct addbefore.';
    }
}
