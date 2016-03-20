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
 * 1. All product types are created.
 *
 * Steps:
 * 1. Navigate to frontend on index page.
 * 2. Input test data into "search field" and press Enter key.
 * 3. Perform all assertions.
 *
 * @group Search_Frontend_(MX)
 * @ZephyrId MAGETWO-25095
 */
class SearchEntityResultsTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    /* end tags */

    /**
     * CMS index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Inject data.
     *
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function __inject(CmsIndex $cmsIndex)
    {
        $this->cmsIndex = $cmsIndex;
    }

    /**
     * Run searching result test.
     *
     * @param CatalogSearchQuery $catalogSearch
     * @return void
     */
    public function test(CatalogSearchQuery $catalogSearch)
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getSearchBlock()->search($catalogSearch->getQueryText());
    }
}
