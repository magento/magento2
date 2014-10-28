<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
