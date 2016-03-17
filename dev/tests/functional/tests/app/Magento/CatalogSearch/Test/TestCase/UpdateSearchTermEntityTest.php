<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\TestCase;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Product is created.
 *
 * Steps:
 * 1. Go to frontend.
 * 2. Test word into the Search field at the top of the page and click Go.
 * 3. Go to backend as admin user.
 * 4. Navigate to Marketing > SEO&Search > Search Terms.
 * 5. Click "Edit" link of just added test word search term.
 * 6. Fill out all data according to dataset.
 * 7. Save the Search Term.
 * 8. Perform all assertions.
 *
 * @group Search_Terms_(MX)
 * @ZephyrId MAGETWO-26100
 */
class UpdateSearchTermEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * CMS index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

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
     * @param CmsIndex $cmsIndex
     * @param CatalogSearchIndex $indexPage
     * @param CatalogSearchEdit $editPage
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogSearchIndex $indexPage,
        CatalogSearchEdit $editPage
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->indexPage = $indexPage;
        $this->editPage = $editPage;
    }

    /**
     * Run update search term test.
     *
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function test(CatalogSearchQuery $searchTerm)
    {
        // Preconditions
        $searchText = $searchTerm->getQueryText();
        // Steps
        $this->cmsIndex->open()->getSearchBlock()->search($searchText);
        $this->indexPage->open()->getGrid()->searchAndOpen(['search_query' => $searchText]);
        $this->editPage->getForm()->fill($searchTerm);
        $this->editPage->getFormPageActions()->save();
    }
}
