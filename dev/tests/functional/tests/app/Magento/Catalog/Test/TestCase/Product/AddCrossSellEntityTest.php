<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple\CrossSellProducts;

/**
 * Class AddCrossSellEntityTest
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create cross cell products
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Products > Catalog
 * 3. Click Add new product
 * 4. Fill data from dataSet
 * 5. Save product
 * 6. Perform all assertions
 *
 * @group Cross-sells_(MX)
 * @ZephyrId MAGETWO-29081
 */
class AddCrossSellEntityTest extends AbstractAddRelatedProductsEntityTest
{
    /**
     * Run test add cross sell products entity
     *
     * @param string $productData
     * @param string $crossSellProductsData
     * @return array
     */
    public function test($productData, $crossSellProductsData)
    {
        $product = $this->getProductByData($productData, ['cross_sell_products' => $crossSellProductsData]);
        $this->createAndSaveProduct($product);

        /** @var CrossSellProducts $crossSellProducts */
        $crossSellProducts = $product->getDataFieldConfig('cross_sell_products')['source'];
        return [
            'product' => $product,
            'relatedProducts' => $crossSellProducts->getProducts()
        ];
    }
}
