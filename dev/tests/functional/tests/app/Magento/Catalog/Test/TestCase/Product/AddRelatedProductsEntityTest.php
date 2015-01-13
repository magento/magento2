<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple\RelatedProducts;

/**
 * Class AddRelatedProductsEntityTest
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create simple Product
 * 2. Create Configurable Product
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Products> Catalog
 * 3. Add Product
 * 4. Fill data according to dataSet
 * 5. Save product
 * 6. Perform all assertions
 *
 * @group Related_Products_(MX)
 * @ZephyrId MAGETWO-29352
 */
class AddRelatedProductsEntityTest extends AbstractAddRelatedProductsEntityTest
{
    /**
     * Run test add related products entity
     *
     * @param string $productData
     * @param string $relatedProductsData
     * @return array
     */
    public function test($productData, $relatedProductsData)
    {
        $product = $this->getProductByData($productData, ['related_products' => $relatedProductsData]);
        $this->createAndSaveProduct($product);

        /** @var RelatedProducts $relatedProducts */
        $relatedProducts = $product->getDataFieldConfig('related_products')['source'];
        return [
            'product' => $product,
            'relatedProducts' => $relatedProducts->getProducts()
        ];
    }
}
