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
