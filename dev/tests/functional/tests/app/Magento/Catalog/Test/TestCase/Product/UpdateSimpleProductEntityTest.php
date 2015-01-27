<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;

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
 * @group Products_(MX)
 * @ZephyrId MAGETWO-23544
 */
class UpdateSimpleProductEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

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
     * Injection data
     *
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @return void
     */
    public function __inject(
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage
    ) {
        $this->productGrid = $productGrid;
        $this->editProductPage = $editProductPage;
    }

    /**
     * Run update product simple entity test
     *
     * @param string $initialProduct
     * @param CatalogProductSimple $product
     * @throws \Exception
     * @return array
     */
    public function test($initialProduct, CatalogProductSimple $product)
    {
        $createProductsStep = ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\TestStep\CreateProductStep',
            ['product' => $initialProduct]
        );
        /** @var CatalogProductSimple $initialProduct */
        $initialProduct = $createProductsStep->run()['product'];
        $filter = ['sku' => $initialProduct->getSku()];

        $this->productGrid->open()->getProductGrid()->searchAndOpen($filter);
        $this->editProductPage->getProductForm()->fill($product);
        $this->editProductPage->getFormPageActions()->save();

        $sharedArguments = ['initialProduct' => $initialProduct];
        $productWithCategory = null;
        if ($product->hasData('category_ids')) {
            $productWithCategory = $product;
        } elseif ($initialProduct->hasData('category_ids')) {
            $productWithCategory = $initialProduct;
        }
        if ($productWithCategory) {
            $categories = $productWithCategory->getDataFieldConfig('category_ids')['source']->getCategories();
            $sharedArguments['category'] = reset($categories);
        }
        return $sharedArguments;
    }
}
