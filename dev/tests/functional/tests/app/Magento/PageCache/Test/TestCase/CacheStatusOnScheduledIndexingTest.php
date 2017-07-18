<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Indexer\Test\Page\Adminhtml\IndexManagement;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Steps:
 * 1. Set all indexers to Update on Schedule mode.
 * 2. Clear all caches.
 * 3. Create a category.
 * 4. Add some products to the category.
 * 5. Perform asserts.
 *
 * @ZephyrId MAGETWO-45833
 */
class CacheStatusOnScheduledIndexingTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Index Management page.
     *
     * @var IndexManagement
     */
    private $indexManagement;

    /**
     * Category Edit page.
     *
     * @var CatalogCategoryEdit
     */
    private $categoryEdit;

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Cli command to do operations with cache.
     *
     * @var Cache
     */
    private $cache;

    /**
     * Inject pages.
     *
     * @param IndexManagement $indexManagement
     * @param CatalogCategoryEdit $categoryEdit
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param Cache $cache
     * @return void
     */
    public function __inject(
        IndexManagement $indexManagement,
        CatalogCategoryEdit $categoryEdit,
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        Cache $cache
    ) {
        $this->indexManagement = $indexManagement;
        $this->categoryEdit = $categoryEdit;
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
        $this->cache = $cache;
    }

    /**
     * Create category with products and verify cache invalidation.
     *
     * @param Category $initialCategory
     * @param Category $category
     * @return array
     */
    public function test(Category $initialCategory, Category $category)
    {
        $this->indexManagement->open();
        $this->indexManagement->getMainBlock()->massaction([], 'Update by Schedule', false, 'Select All');
        $initialCategory->persist();
        $this->cache->flush();

        $this->browser->open($_ENV['app_frontend_url'] . $initialCategory->getUrlKey() . '.html');
        $this->categoryEdit->open(['id' => $initialCategory->getId()]);
        $this->categoryEdit->getEditForm()->fill($category);
        $this->categoryEdit->getFormPageActions()->save();

        $products = $category->getDataFieldConfig('category_products')['source']->getProducts();
        return [
            'category' => $this->fixtureFactory->createByCode(
                'category',
                [
                    'data' => array_merge(
                        $initialCategory->getData(),
                        $category->getData(),
                        ['category_products' => ['products' => $products]]
                    )
                ]
            ),
        ];
    }

    /**
     * Restore indexers mode.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->indexManagement->open();
        $this->indexManagement->getMainBlock()->massaction([], 'Update on Save', false, 'Select All');
    }
}
