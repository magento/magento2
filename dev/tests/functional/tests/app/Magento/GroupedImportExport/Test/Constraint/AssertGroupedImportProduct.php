<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedImportExport\Test\Constraint;

use Magento\CatalogImportExport\Test\Constraint\AssertImportProduct;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert products data from csv import file and page are match.
 */
class AssertGroupedImportProduct extends AssertImportProduct
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
    protected function getPrepareProductsData(FixtureInterface $product)
    {
        $productId = $this->retrieveProductBySku($product)['id'];
        $this->catalogProductEdit->open(['id' => $productId]);
        $productData = $this->catalogProductEdit->getProductForm()->getData($product);
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
        return 'Imported grouped products data from csv are correct.';
    }
}
