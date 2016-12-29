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
 * Test Creation for Update DownloadableProductEntity.
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
     * Downloadable product fixture.
     *
     * @var DownloadableProduct
     */
    private $product;

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * Edit product page on backend.
     *
     * @var CatalogProductEdit
     */
    private $catalogProductEdit;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Persist category.
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
     * Filling objects of the class.
     *
     * @param CatalogProductIndex $catalogProductIndexNewPage
     * @param CatalogProductEdit $catalogProductEditPage
     * @param FixtureFactory $fixtureFactory
     * @return array
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
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductIndex = $catalogProductIndexNewPage;
        $this->catalogProductEdit = $catalogProductEditPage;
    }

    /**
     * Test update downloadable product.
     *
     * @param DownloadableProduct $product
     * @param Category $category
     * @param string $storeDataset [optional]
     * @param int $storesCount [optional]
     * @param int|null $storeIndexToUpdate [optional]
     * @return array
     */
    public function test(
        DownloadableProduct $product,
        Category $category,
        $storeDataset = '',
        $storesCount = 0,
        $storeIndexToUpdate = null
    ) {
        // Preconditions
        $stores = [];
        if ($storeDataset) {
            for ($i = 0; $i < $storesCount; $i++) {
                $stores[$i] = $this->fixtureFactory->createByCode('store', ['dataset' => $storeDataset]);
                $stores[$i]->persist();
            }
        }

        // Test steps
        $filter = ['sku' => $this->product->getSku()];
        $this->catalogProductIndex->open()->getProductGrid()->searchAndOpen($filter);
        if ($storeDataset && $storeIndexToUpdate !== null) {
            $this->catalogProductEdit->getFormPageActions()->changeStoreViewScope($stores[$storeIndexToUpdate]);
        }
        $productBlockForm = $this->catalogProductEdit->getProductForm();
        $productBlockForm->fill($product, null, $category);
        $this->catalogProductEdit->getFormPageActions()->save();

        return [
            'store' => $storeDataset ? $stores[$storeIndexToUpdate] : '',
            'initialProduct' => $this->product
        ];
    }
}
