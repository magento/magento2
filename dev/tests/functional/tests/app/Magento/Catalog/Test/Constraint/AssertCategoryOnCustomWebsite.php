<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert category name on custom website store.
 */
class AssertCategoryOnCustomWebsite extends AbstractConstraint
{
    /**
     * Assert that category name is correct on custom website store.
     *
     * @param FixtureFactory $fixtureFactory
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @param Category $category
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        BrowserInterface $browser,
        CatalogCategoryView $categoryView,
        Category $category
    ) {
        $storeGroup = $fixtureFactory->createByCode(
            'storeGroup',
            [
                'dataset' => 'custom_new_group',
                'data' => [
                    'root_category_id' => [
                        'category' => $category->getDataFieldConfig('parent_id')['source']->getParentCategory()
                    ]
                ]
            ]
        );
        $storeGroup->persist();
        $store = $fixtureFactory->createByCode(
            'store',
            [
                'dataset' => 'custom_store',
                'data' => [
                    'group_id' => [
                        'storeGroup' => $storeGroup
                    ]
                ]
            ]
        );
        $store->persist();

        $websiteCode = $storeGroup->getDataFieldConfig('website_id')['source']->getWebsite()->getData('code');
        $browser->open($_ENV['app_frontend_url'] . 'websites/' . $websiteCode . '/' . $category->getName() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            $category->getName(),
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong category name is displayed on custom website store.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category name is correct on custom website store.';
    }
}
