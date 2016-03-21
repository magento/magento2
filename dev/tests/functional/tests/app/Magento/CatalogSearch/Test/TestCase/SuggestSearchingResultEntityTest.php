<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\TestCase;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Two "default" test simple products is created.
 * 2. Navigate to frontend.
 * 3. Input in "Search" field(top-right part of the index page, near cart icon) 'Simple' and press "Enter" key.
 *
 * Steps:
 * 1. Go to frontend on index page.
 * 2. Input in "Search" field test data.
 * 3. Perform asserts.
 *
 * @group Search_Frontend_(CS)
 * @ZephyrId MAGETWO-24671
 */
class SuggestSearchingResultEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Run suggest searching result test.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogSearchQuery $catalogSearch
     * @return void
     */
    public function testSearch(CmsIndex $cmsIndex, CatalogSearchQuery $catalogSearch)
    {
        $cmsIndex->open();
        $cmsIndex->getSearchBlock()->search($catalogSearch->getQueryText());
    }
}
