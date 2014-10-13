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

use Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
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
class AddUpSellEntityTest extends Injectable
{
    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog product index page on backend
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Catalog product view page on backend
     *
     * @var CatalogProductNew
     */
    protected $catalogProductNew;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Inject data
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductNew $catalogProductNew
     * @return void
     */
    public function __inject(CatalogProductIndex $catalogProductIndex, CatalogProductNew $catalogProductNew)
    {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductNew = $catalogProductNew;
    }

    /**
     * Run test add up sell entity
     *
     * @param string $productData
     * @param string $upSellProducts
     * @return array
     */
    public function test($productData, $upSellProducts)
    {
        $product = $this->createProduct($productData, $upSellProducts);
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;

        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getGridPageActionBlock()->addProduct($typeId);
        $this->catalogProductNew->getProductForm()->fill($product);
        $this->catalogProductNew->getFormPageActions()->save($product);

        /** @var UpSellProducts $upSellProducts*/
        $upSellProducts = $product->getDataFieldConfig('up_sell_products')['source'];
        return [
            'product' => $product,
            'relatedProducts' => $upSellProducts->getProducts()
        ];
    }

    /**
     * Create product
     *
     * @param string $productData
     * @param string $upSellProducts
     * @return FixtureInterface
     */
    protected function createProduct($productData, $upSellProducts)
    {
        list($fixtureCode, $dataSet) = explode('::', $productData);
        return $this->fixtureFactory->createByCode(
            $fixtureCode,
            [
                'dataSet' => $dataSet,
                'data' => [
                    'up_sell_products' => [
                        'presets' => $upSellProducts
                    ]
                ]
            ]
        );
    }
}
