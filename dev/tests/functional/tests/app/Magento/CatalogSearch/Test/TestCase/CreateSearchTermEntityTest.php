<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\TestCase;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Product is created.
 *
 * Steps:
 * 1. Go to backend as admin user.
 * 4. Navigate to Marketing > SEO&Search > Search Terms.
 * 5. Click "Add New Search Term" button.
 * 6. Fill out all data according to dataset.
 * 7. Save the Search Term.
 * 8. Perform all assertions.
 *
 * @group Search_Terms_(MX)
 * @ZephyrId MAGETWO-26165
 */
class CreateSearchTermEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Search term page.
     *
     * @var CatalogSearchIndex
     */
    protected $indexPage;

    /**
     * Search term edit page.
     *
     * @var CatalogSearchEdit
     */
    protected $editPage;

    /**
     * Inject pages.
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
     * Run create search term test.
     *
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function test(CatalogSearchQuery $searchTerm)
    {
        // Steps
        $this->indexPage->open();
        $this->indexPage->getGridPageActions()->addNew();
        $this->editPage->getForm()->fill($searchTerm);
        $this->editPage->getFormPageActions()->save();
    }
}
