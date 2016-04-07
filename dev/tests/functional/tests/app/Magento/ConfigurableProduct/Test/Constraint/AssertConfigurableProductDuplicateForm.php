<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert form data equals duplicate product configurable data.
 */
class AssertConfigurableProductDuplicateForm extends AssertConfigurableProductForm
{
    /**
     * Assert form data equals duplicate product configurable data.
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
        $product = $this->processFixture($product);
        $duplicateProductSku = $product->getSku() . '-1';
        $filter = ['sku' => $duplicateProductSku];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filter);

        $productData = $product->getData();
        $productData['sku'] = $duplicateProductSku;
        $productData['status'] = 'No';
        if (isset($compareData['quantity_and_stock_status']['qty'])) {
            $compareData['quantity_and_stock_status']['qty'] = '';
            $compareData['quantity_and_stock_status']['is_in_stock'] = 'Out of Stock';
        }
        $fixtureData = $this->prepareFixtureData($productData, $this->sortFields);
        $formData = $this->prepareFormData($productPage->getProductForm()->getData($product), $this->sortFields);
        $error = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertTrue(empty($error), $error);
    }

    /**
     * Prepares fixture data for comparison.
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFixtureData(array $data, array $sortFields = [])
    {
        $data['url_key'] = $this->prepareUrlKey($data['url_key']);
        $data['quantity_and_stock_status']['is_in_stock'] = 'Out of Stock';
        return parent::prepareFixtureData($data, $sortFields);
    }

    /**
     * Prepare url key.
     *
     * @param string $urlKey
     * @return string
     */
    protected function prepareUrlKey($urlKey)
    {
        preg_match("~\d+$~", $urlKey, $matches);
        $key = intval($matches[0]) + 1;
        return str_replace($matches[0], $key, $urlKey);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Form data equals to fixture data of duplicated product.';
    }
}
