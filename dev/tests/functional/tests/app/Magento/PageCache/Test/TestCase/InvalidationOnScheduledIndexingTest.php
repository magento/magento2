<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Indexer\Test\Page\Adminhtml\IndexManagement;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Client\BrowserInterface;
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
class InvalidationOnScheduledIndexingTest extends Injectable
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
     * Inject pages.
     *
     * @param IndexManagement $indexManagement
     * @param CatalogCategoryEdit $categoryEdit
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        IndexManagement $indexManagement,
        CatalogCategoryEdit $categoryEdit,
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory
    ) {
        $this->indexManagement = $indexManagement;
        $this->categoryEdit = $categoryEdit;
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create category with products and verify cache invalidation.
     *
     * @param Category $category
     * @param string $productsList
     * @return array
     */
    public function test(Category $category, $productsList)
    {
        //Preconditions
        $this->indexManagement->open();
        $this->indexManagement->getMainBlock()->massaction([], 'Update by Schedule', false, 'Select All');
        $category->persist();

        //Steps
        $this->browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        $categoryFixture = $this->fixtureFactory->createByCode(
            'category',
            [
                'data' => array_merge(
                    $category->getData(),
                    ['category_products' => ['dataset' => $productsList]]
                )
            ]
        );
        $this->categoryEdit->open(['id' => $category->getId()]);
        $this->categoryEdit->getEditForm()->fill($categoryFixture);
        $this->categoryEdit->getFormPageActions()->save();

        return [
            'category' => $categoryFixture,
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
