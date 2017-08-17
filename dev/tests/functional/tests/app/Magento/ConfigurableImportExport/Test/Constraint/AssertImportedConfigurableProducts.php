<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableImportExport\Test\Constraint;

use Magento\CatalogImportExport\Test\Constraint\AssertImportedProducts;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that products data from CSV import file and data from product edit page are correct and match.
 */
class AssertImportedConfigurableProducts extends AssertImportedProducts
{
    /**
     * Product type.
     *
     * @var string
     */
    protected $productType = 'configurable';

    /**
     * Needed configurable product data.
     *
     * @var array
     */
    protected $neededKeys = [
        'sku',
        'name',
        'additional_attributes',
        'configurable_variations',
        'url_key',
    ];

    /**
     * Prepare configurable product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getDisplayedProductData(FixtureInterface $product)
    {
        $productData = $this->getDisplayedOnProductPageData($product);
        $attributesData = $productData['configurable_attributes_data']['matrix']['0'];
        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('variations');
        $productAttribute = $form->getSection('variations')->getVariationsBlock()->getProductAttribute();
        $productAttribute = str_replace(': ', '=', $productAttribute);
        $productData['additional_attributes'] = $productAttribute;
        $productData['configurable_variations'] = 'sku=' . $attributesData['sku'] . ',' . $productAttribute;
        unset($productData['configurable_attributes_data']);

        return $this->getResultProductsData($productData);
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products data from CSV import file and data from product edit page are correct and match.';
    }
}
