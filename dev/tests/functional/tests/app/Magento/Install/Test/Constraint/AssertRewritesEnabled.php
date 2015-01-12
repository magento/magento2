<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Client\Driver\Selenium\Browser;
use Magento\Catalog\Test\Fixture\CatalogCategory;

/**
 * Assert that apache redirect correct works.
 */
class AssertRewritesEnabled extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that apache redirect works by opening category page and asserting index.php in its url
     *
     * @param CatalogCategory $category
     * @param CmsIndex $homePage
     * @param Browser $browser
     */
    public function processAssert(CatalogCategory $category, CmsIndex $homePage, Browser $browser)
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
