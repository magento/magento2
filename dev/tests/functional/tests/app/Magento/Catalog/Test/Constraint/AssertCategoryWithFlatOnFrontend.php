<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Config\Test\TestStep\SetupConfigurationStep;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Assert that category with flat enabled is present on frontend.
 */
class AssertCategoryWithFlatOnFrontend extends AssertCategoryOnFrontend
{
    /**
     * Assert that category with flat enabled is present on frontend.
     *
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @param Category $category
     * @param Category $initialCategory
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogCategoryView $categoryView,
        Category $category,
        Category $initialCategory,
        CmsIndex $cmsIndex
    ) {
        /** @var TestStepFactory $testStepFactory */
        $testStepFactory = $this->objectManager->get(TestStepFactory::class);

        /** @var SetupConfigurationStep $configurationStep */
        $configurationStep = $testStepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => 'category_flat']
        );
        $configurationStep->run();

        /** @var AdminCache $adminCache */
        $adminCache = $this->objectManager->get(AdminCache::class);
        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        parent::processAssert($browser, $categoryView, $category, $initialCategory, $cmsIndex);

        $configurationStep->cleanup();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category with flat enabled is present on frontend.';
    }
}
