<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that link "Add Products Manually" is shown after all variations are deleted.
 */
class AssertAddProductsManually extends AbstractConstraint
{
    /**
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
        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filter);

        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\ProductForm $productForm */
        $productForm = $productPage->getProductForm()->openTab('variations');
        /** @var Config $variationsTab */
        $variationsTab = $productForm->getTab('variations');
        $variationsTab->deleteAttributes();
        $configurableAttributesData = $product->getConfigurableAttributesData();
        $errors = $variationsTab->addProductsManually($configurableAttributesData);
        $productPage->getFormPageActions()->save($product);

        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(' ', $errors));
    }

    /**
     * Returns a string representation of the object,
     *
     * @return string
     */
    public function toString()
    {
        return 'Link "Add Products Manually" is shown after all variations are deleted.';
    }
}
