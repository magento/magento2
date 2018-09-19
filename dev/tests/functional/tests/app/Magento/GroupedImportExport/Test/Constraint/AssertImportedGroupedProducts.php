<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedImportExport\Test\Constraint;

use Magento\CatalogImportExport\Test\Constraint\AssertImportedProducts;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that products data from CSV import file and data from product edit page are correct and match.
 */
class AssertImportedGroupedProducts extends AssertImportedProducts
{
    /**
     * Product type.
     *
     * @var string
     */
    protected $productType = 'grouped';

    /**
     * Needed grouped product data.
     *
     * @var array
     */
    protected $neededKeys = [
        'sku',
        'name',
        'associated_skus',
        'url_key',
    ];

    /**
     * Prepare grouped product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getDisplayedProductData(FixtureInterface $product)
    {
        $productData = $this->getDisplayedOnProductPageData($product);
        $assignedProduct = $productData['associated']['assigned_products'][0];
        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('grouped');
        $attributeSku = $form->getSection('grouped')->getListAssociatedProductsBlock()->getAssociatedProductSku()[0];
        $productData['associated_skus'] = $attributeSku . '=' . $assignedProduct['qty'];
        unset($productData['associated']);

        return $this->getResultProductsData($productData);
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that product data from CSV import file and data from product edit page are correct and match.';
    }
}
