<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Assert that apache redirect correct works.
 */
class AssertRewritesEnabled extends AbstractConstraint
{
    /**
     * Assert that apache redirect works by opening category page and asserting index.php in its url
     *
     * @param Category $category
     * @param CmsIndex $homePage
     * @param BrowserInterface $browser
     */
    public function processAssert(Category $category, CmsIndex $homePage, BrowserInterface $browser)
    {
        $category->persist();
        $homePage->open();
        $homePage->getTopmenu()->selectCategoryByName($category->getName());

        \PHPUnit_Framework_Assert::assertTrue(
            strpos($browser->getUrl(), 'index.php') === false,
            'Apache redirect for category does not work.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Apache redirect works correct.';
    }
}
