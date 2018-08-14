<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductForm;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertConfigurableProductForm
 * Assert form data equals fixture data
 */
class AssertConfigurableProductForm extends AssertProductForm
{
    /**
     * List skipped fixture fields in verify
     *
     * @var array
     */
    protected $skippedFixtureFields = [
        'id',
        'affected_attribute_set',
        'checkout_data',
        'price'
    ];

    /**
     * List skipped attribute fields in verify
     *
     * @var array
     */
    protected $skippedAttributeFields = [
        'frontend_input',
        'frontend_label',
        'attribute_code',
        'attribute_id',
        'is_required',
    ];

    /**
     * List skipped option fields in verify
     *
     * @var array
     */
    protected $skippedOptionFields = [
        'admin',
        'id',
        'is_default',
    ];

    /**
     * Skipped variation matrix field.
     *
     * @var array
     */
    protected $skippedVariationMatrixFields = [
        'configurable_attribute',
    ];

    /**
     * Prepares fixture data for comparison
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFixtureData(array $data, array $sortFields = [])
    {
        // Attribute is no longer displayed on product page
        unset($data['configurable_attributes_data']['attributes_data']);

        // prepare and filter values, reset keys in variation matrix
        $variationsMatrix = $data['configurable_attributes_data']['matrix'];
        foreach ($variationsMatrix as $key => $variationMatrix) {
            $variationsMatrix[$key] = array_diff_key($variationMatrix, array_flip($this->skippedVariationMatrixFields));
        }
        $data['configurable_attributes_data']['matrix'] = array_values($variationsMatrix);

        return parent::prepareFixtureData($data, $sortFields);
    }

    /**
     * Prepares form data for comparison
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFormData(array $data, array $sortFields = [])
    {
        // filter values and reset keys in variation matrix
        $variationsMatrix = $this->trimCurrencyForPriceInMatrix($data['configurable_attributes_data']['matrix']);
        foreach ($variationsMatrix as $key => $variationMatrix) {
            $variationsMatrix[$key] = array_diff_key($variationMatrix, array_flip($this->skippedVariationMatrixFields));
        }
        $data['configurable_attributes_data']['matrix'] = array_values($variationsMatrix);

        foreach ($sortFields as $path) {
            $data = $this->sortDataByPath($data, $path);
        }
        return $data;
    }

    /**
     * Escape currency for price in matrix
     *
     * @param array $variationsMatrix
     * @param string $currency
     * @return array
     */
    protected function trimCurrencyForPriceInMatrix($variationsMatrix, $currency = '$')
    {
        foreach ($variationsMatrix as &$variation) {
            if (isset($variation['price'])) {
                $variation['price'] = str_replace($currency, '', $variation['price']);
            }
        }
        return $variationsMatrix;
    }

    /**
     * Assert form data equals product configurable data.
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
        parent::processAssert($product, $productGrid, $productPage);
    }

    /**
     * Remove price\special price fields from fixture as it should not be retrieved from product form
     *
     * @param FixtureInterface $product
     * @return mixed
     */
    protected function processFixture(FixtureInterface $product)
    {
        $data = array_diff_key($product->getData(), ['price' => 0, 'special_price' => 0]);
        return $this->objectManager->create(
            \Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct::class,
            ['data' => $data]
        );
    }
}
