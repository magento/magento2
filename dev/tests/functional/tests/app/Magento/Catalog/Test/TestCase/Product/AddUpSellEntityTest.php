<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple\UpSellProducts;

/**
 * Class AddUpSellEntityTest
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create related products
 *
 * Steps:
 * 1. Login to the backend
 * 2. Navigate to Products > Catalog
 * 3. Start to create product according to dataset
 * 4. Save product
 * 5. Perform appropriate assertions
 *
 * @group Up-sells_(MX)
 * @ZephyrId MAGETWO-29105
 */
class AddUpSellEntityTest extends AbstractAddRelatedProductsEntityTest
{
    /**
     * Run test add up sell products entity
     *
     * @param string $productData
     * @param string $upSellProductsData
     * @return array
     */
    public function test($productData, $upSellProductsData)
    {
        $product = $this->getProductByData($productData, ['up_sell_products' => $upSellProductsData]);
        $this->createAndSaveProduct($product);

        /** @var UpSellProducts $upSellProducts */
        $upSellProducts = $product->getDataFieldConfig('up_sell_products')['source'];
        return [
            'product' => $product,
            'relatedProducts' => $upSellProducts->getProducts()
        ];
    }
}
