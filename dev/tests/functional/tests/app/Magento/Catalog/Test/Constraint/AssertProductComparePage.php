<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductComparePage
 * Assert that "Compare Product" page contains product(s) that was added
 */
class AssertProductComparePage extends AbstractConstraint
{
    /**
     * Price displaying format.
     *
     * @var int
     */
    protected $priceFormat = 2;

    /**
     * Product attribute on compare product page
     *
     * @var array
     */
    protected $attributeProduct = [
        'name',
        'price',
        'sku' => 'SKU',
        'description' => 'Description',
        'short_description' => 'Short Description',
    ];

    /**
     * Assert that "Compare Product" Storefront page contains added Products with expected Attribute values:
     * - Name
     * - Price
     * - SKU
     * - Description (if exists, else text "No")
     * - Short Description (if exists, else text "No")
     *
     * @param array $products
     * @param CatalogProductCompare $comparePage
     * @param CmsIndex $cmsIndex
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        array $products,
        CatalogProductCompare $comparePage,
        CmsIndex $cmsIndex
    ) {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->openLink("Compare Products");
        foreach ($products as $key => $product) {
            foreach ($this->attributeProduct as $attributeKey => $attribute) {
                $value = $attribute;
                $attribute = is_numeric($attributeKey) ? $attribute : $attributeKey;

                $expectedAttributeValue = $attribute != 'price'
                    ? ($product->hasData($attribute)
                        ? $product->getData($attribute)
                        : 'N/A')
                    : ($product->getDataFieldConfig('price')['source']->getPriceData() !== null
                        ? $product->getDataFieldConfig('price')['source']->getPriceData()['compare_price']
                        : number_format($product->getPrice(), $this->priceFormat));

                $attribute = is_numeric($attributeKey) ? 'info' : 'attribute';
                $attribute = ucfirst($attribute);
                $actualAttributeValue =
                    $comparePage->getCompareProductsBlock()->{'getProduct' . $attribute}($key + 1, $value);

                \PHPUnit_Framework_Assert::assertEquals(
                    $expectedAttributeValue,
                    $actualAttributeValue,
                    'Product "' . $product->getName() . '" has "' . $attribute . '" value different from fixture one.'
                );
            }
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return '"Compare Product" page has valid data for all products.';
    }
}
