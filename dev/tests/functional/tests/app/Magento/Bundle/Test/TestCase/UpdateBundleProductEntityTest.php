<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\TestCase;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Test Flow:
 *
 * Precondition:
 * 1. Category is created.
 * 2. Bundle product is created.
 *
 * Steps
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS > Catalog.
 * 3. Select a product in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save".
 * 6. Perform asserts
 *
 * @group Bundle_Product
 * @ZephyrId MAGETWO-26195
 */
class UpdateBundleProductEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Page product on backend
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Edit page on backend
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Injection data
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
        $this->catalogProductIndex = $catalogProductIndexNewPage;
        $this->catalogProductEdit = $catalogProductEditPage;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test update bundle product
     *
     * @param BundleProduct $product
     * @param BundleProduct $originalProduct
     * @param string $storeDataset [optional]
     * @return array
     */
    public function test(
        BundleProduct $product,
        BundleProduct $originalProduct,
        $storeDataset = ''
    ) {
        // Preconditions
        $originalProduct->persist();
        $originalCategory = $originalProduct->hasData('category_ids')
            ? $originalProduct->getDataFieldConfig('category_ids')['source']->getCategories()
            : null;
        $category = $product->hasData('category_ids')
            ? $product->getDataFieldConfig('category_ids')['source']->getCategories()
            : $originalCategory;

        if ($storeDataset) {
            $store = $this->fixtureFactory->createByCode('store', ['dataset' => $storeDataset]);
            $store->persist();
            $optionTitle[$store->getStoreId()] = $product->getBundleSelections()['bundle_options'][0]['title'];
        }

        // Steps
        $filter = ['sku' => $originalProduct->getSku()];

        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
        if ($storeDataset) {
            $this->catalogProductEdit->getFormPageActions()->changeStoreViewScope($store);
        }
        $this->catalogProductEdit->getProductForm()->fill($product);
        $this->catalogProductEdit->getFormPageActions()->save();

        return [
            'category' => $category,
            'stores' => isset($store) ? [$store] : [],
            'optionTitles' => isset($optionTitle) ? $optionTitle : []
        ];
    }
}
