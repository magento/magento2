<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\TestCase;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteSearchTermEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Product is created
 *
 * Steps:
 * 1. Go to backend as admin user
 * 2. Navigate to Marketing>SEO & Search>Search
 * 3. Search and open Search Term by "Search Query"
 * 4. Click "Delete Search" button
 * 5. Perform all assertions
 *
 * @group Search_Terms
 * @ZephyrId MAGETWO-26491
 */
class DeleteSearchTermEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Search term page
     *
     * @var CatalogSearchIndex
     */
    protected $indexPage;

    /**
     * Search term edit page
     *
     * @var CatalogSearchEdit
     */
    protected $editPage;

    /**
     * Inject pages
     *
     * @param CatalogSearchIndex $indexPage
     * @param CatalogSearchEdit $editPage
     * @return void
     */
    public function __inject(CatalogSearchIndex $indexPage, CatalogSearchEdit $editPage)
    {
        $this->indexPage = $indexPage;
        $this->editPage = $editPage;
    }

    /**
     * Run delete search term entity test
     *
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function test(CatalogSearchQuery $searchTerm)
    {
        // Preconditions
        $searchTerm->persist();
        $searchText = $searchTerm->getQueryText();
        // Steps
        $this->indexPage->open();
        $this->indexPage->getGrid()->searchAndOpen(['search_query' => $searchText]);
        $this->editPage->getFormPageActions()->delete();
        $this->editPage->getModalBlock()->acceptAlert();
    }
}
