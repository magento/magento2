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
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Test Creation for CreateSimpleProductEntity
 *
 * Test Flow:
 * 1. Login to the backend.
 * 2. Navigate to Products > Catalog.
 * 3. Start to create simple product.
 * 4. Fill in data according to data set.
 * 5. Save Product.
 * 6. Perform appropriate assertions.
 *
 * @group Products_(CS)
 * @ZephyrId MAGETWO-23414
 */
class CreateSimpleProductEntityTest extends Injectable
{
    /**
     * Category fixture
     *
     * @var CatalogCategory
     */
    protected $category;

    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to create a product
     *
     * @var CatalogProductNew
     */
    protected $newProductPage;

    /**
     * Prepare data
     *
     * @param CatalogCategory $category
     * @return array
     */
    public function __prepare(CatalogCategory $category)
    {
        $category->persist();

        return [
            'category' => $category
        ];
    }

    /**
     * Injection data
     *
     * @param CatalogCategory $category
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @return void
     */
    public function __inject(
        CatalogCategory $category,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage
    ) {
        $this->category = $category;
        $this->productGrid = $productGrid;
        $this->newProductPage = $newProductPage;
    }

    /**
     * Run create product simple entity test
     *
     * @param CatalogProductSimple $product
     * @param CatalogCategory $category
     * @return void
     */
    public function testCreate(CatalogProductSimple $product, CatalogCategory $category)
    {
        // Steps
        $this->productGrid->open();
        $this->productGrid->getGridPageActionBlock()->addProduct('simple');
        $this->newProductPage->getProductForm()->fill($product, null, $category);
        $this->newProductPage->getFormPageActions()->save();
    }
}
