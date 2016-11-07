<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for Update DownloadableProductEntity
 *
 * Test Flow:
 *
 * Precondition:
 * 1. Category is created.
 * 2. Product is created(before each variation).
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS > Catalog.
 * 3. Search and open product in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save".
 * 6. Perform asserts.
 *
 * @group Downloadable_Product
 * @ZephyrId MAGETWO-24775
 */
class UpdateDownloadableProductEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TO_MAINTAIN = 'yes';
    /* end tags */

    /**
     * Downloadable product fixture
     *
     * @var DownloadableProduct
     */
    protected $product;

    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Edit product page on backend
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Persist category
     *
     * @param Category $category
     * @return array
     */
    public function __prepare(Category $category)
    {
        $category->persist();
        return [
            'category' => $category
        ];
    }

    /**
     * Filling objects of the class
     *
     * @param CatalogProductIndex $catalogProductIndexNewPage
     * @param CatalogProductEdit $catalogProductEditPage
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndexNewPage,
        CatalogProductEdit $catalogProductEditPage,
        FixtureFactory $fixtureFactory
    ) {
        $this->product = $fixtureFactory->createByCode(
            'downloadableProduct',
            ['dataset' => 'default']
        );
        $this->product->persist();
        $this->catalogProductIndex = $catalogProductIndexNewPage;
        $this->catalogProductEdit = $catalogProductEditPage;
    }

    /**
     * Test update downloadable product
     *
     * @param DownloadableProduct $product
     * @param Category $category
     * @return void
     */
    public function test(DownloadableProduct $product, Category $category)
    {
        // Steps
        $filter = ['sku' => $this->product->getSku()];
        $this->catalogProductIndex->open()->getProductGrid()->searchAndOpen($filter);
        $productBlockForm = $this->catalogProductEdit->getProductForm();
        $productBlockForm->fill($product, null, $category);
        $this->catalogProductEdit->getFormPageActions()->save();
    }
}
