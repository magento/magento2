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
use Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Test Creation for UpdateProductSimpleEntity
 *
 * Test Flow:
 *
 * Precondition:
 * Category is created.
 * Product is created and assigned to created category.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS -> Catalog.
 * 3. Select a product in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save".
 * 6. Perform asserts.
 *
 * @group Products_(CS)
 * @ZephyrId MAGETWO-23544
 */
class UpdateSimpleProductEntityTest extends Injectable
{
    /**
     * Simple product fixture
     *
     * @var CatalogProductSimple
     */
    protected $product;

    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to update a product
     *
     * @var CatalogProductEdit
     */
    protected $editProductPage;

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
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @param CatalogCategory $category
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage,
        CatalogCategory $category,
        FixtureFactory $fixtureFactory
    ) {
        $this->product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'default',
                'data' => [
                    'category_ids' => [
                        'category' => $category
                    ]
                ]
            ]
        );
        $this->product->persist();

        $this->productGrid = $productGrid;
        $this->editProductPage = $editProductPage;
    }

    /**
     * Run update product simple entity test
     *
     * @param CatalogProductSimple $product
     * @return void
     */
    public function testUpdate(CatalogProductSimple $product)
    {
        $filter = ['sku' => $this->product->getSku()];
        $this->productGrid->open()->getProductGrid()->searchAndOpen($filter);
        $this->editProductPage->getProductForm()->fill($product);
        $this->editProductPage->getFormPageActions()->save();
    }
}
