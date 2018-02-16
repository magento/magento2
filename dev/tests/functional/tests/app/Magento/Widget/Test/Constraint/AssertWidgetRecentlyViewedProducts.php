<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that that widget with type Recently Viewed Products is present on category page
 */
class AssertWidgetRecentlyViewedProducts extends AbstractConstraint
{
    /**
     * Browser
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Category Page on Frontend
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Assert that widget with type Recently Viewed Products is present on category page
     *
     * @param CmsIndex $cmsIndex
     * @param AdminCache $adminCache
     * @param CatalogCategoryView $catalogCategoryView
     * @param BrowserInterface $browser
     * @param CatalogProductSimple $productSimple
     * @param Category $category
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        AdminCache $adminCache,
        CatalogCategoryView $catalogCategoryView,
        BrowserInterface $browser,
        CatalogProductSimple $productSimple,
        Category $category,
        Customer $customer
    ) {
        $this->browser = $browser;
        $this->cmsIndex = $cmsIndex;
        $this->catalogCategoryView = $catalogCategoryView;

        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        // Log in customer
        $customer->persist();
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();

        // Open products
        $productSimple->persist();
        $category->persist();
        $this->browser->open($_ENV['app_frontend_url'] . $productSimple->getUrlKey() . '.html');
        $this->checkRecentlyViewedBlockOnCategory($productSimple, $category);
    }

    /**
     * Check that block Recently Viewed contains product on category page
     *
     * @param CatalogProductSimple $productSimple
     * @param Category $category
     * @return void
     */
    protected function checkRecentlyViewedBlockOnCategory(
        CatalogProductSimple $productSimple,
        Category $category
    ) {
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($category->getName());

        $products = $this->catalogCategoryView->getViewBlock()->getProductsFromRecentlyViewedBlock();
        \PHPUnit_Framework_Assert::assertTrue(
            in_array($productSimple->getName(), $products),
            'Product' . $productSimple->getName() . ' is absent on Recently Viewed block on Category page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Widget with type Recently Viewed Products is present on Category page.";
    }
}
