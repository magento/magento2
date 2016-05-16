<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Fixture\CmsBlock;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert that created CMS block displayed on frontend category page.
 */
class AssertCmsBlockOnCategoryPage extends AbstractConstraint
{
    /**
     * Assert that created CMS block displayed on frontend category page (in order to assign block to category:
     * go to category page> Display settings> CMS Block).
     *
     * @param CmsIndex $cmsIndex
     * @param CmsBlock $cmsBlock
     * @param CatalogCategoryView $catalogCategoryView
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CmsBlock $cmsBlock,
        CatalogCategoryView $catalogCategoryView,
        FixtureFactory $fixtureFactory
    ) {
        $category = $fixtureFactory->createByCode(
            'category',
            [
                'dataset' => 'default_subcategory',
                'data' => [
                    'display_mode' => 'Static block and products',
                    'landing_page' => $cmsBlock->getTitle(),
                ]
            ]
        );
        $category->persist();

        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($category->getName());
        $categoryViewContent = $catalogCategoryView->getViewBlock()->getContent();

        \PHPUnit_Framework_Assert::assertEquals(
            $cmsBlock->getContent(),
            $categoryViewContent,
            'Wrong block content on category is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'CMS block description is present on Category page (frontend).';
    }
}
