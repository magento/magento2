<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\Store\Test\Fixture\Store;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Update simple products data in configurable for store.
 */
class UpdateSimplesInConfigurablePerStoreStep implements TestStepInterface
{
    /**
     * Store View data.
     *
     * @var Store
     */
    private $store;

    /**
     * Indexer.
     *
     * @var Indexer
     */
    private $indexer;

    /**
     * Magento Cache.
     *
     * @var Cache
     */
    private $cache;

    /**
     * Catalog product edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Product grid page.
     *
     * @var CatalogProductIndex
     */
    private $productGrid;

    /**
     * Configurable product fixture.
     *
     * @var ConfigurableProduct
     */
    protected $product;

    /**
     * Simple product fixture.
     *
     * @var CatalogProductSimple
     */
    protected $updatedSimple;

    /**
     * ChangeWebsitePriceStep constructor.
     * @param Indexer $indexer
     * @param Cache $cache
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $catalogProductEdit
     * @param ConfigurableProduct $product
     * @param CatalogProductSimple $updatedSimple
     * @param Store $store
     */
    public function __construct(
        Indexer $indexer,
        Cache $cache,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $catalogProductEdit,
        ConfigurableProduct $product,
        CatalogProductSimple $updatedSimple,
        Store $store
    ) {
        $this->indexer = $indexer;
        $this->cache = $cache;
        $this->store = $store;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->productGrid = $productGrid;
        $this->product = $product;
        $this->updatedSimple = $updatedSimple;
    }

    /**
     * Update simple products data in configurable for store.
     *
     * @return void
     */
    public function run()
    {
        if (!$this->store) {
            return;
        }

        $simpleProductsArray = [];
        $configurableAttributes = $this->product->getConfigurableAttributesData();

        if (isset($configurableAttributes['matrix'])) {
            foreach ($configurableAttributes['matrix'] as $matrixItem) {
                $simpleProductsArray[] = $matrixItem['sku'];
            }
        }

        foreach ($simpleProductsArray as $simpleProductSku) {
            //open product
            $filter = ['sku' => $simpleProductSku];
            $this->productGrid->open();
            $this->productGrid->getProductGrid()->searchAndOpen($filter);
            //update
            $this->catalogProductEdit->getFormPageActions()->changeStoreViewScope($this->store);
            $this->catalogProductEdit->getProductForm()->fill($this->updatedSimple);
            $this->catalogProductEdit->getFormPageActions()->save();
        }

        $this->indexer->reindex();
        $this->cache->flush();
    }
}
