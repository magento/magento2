<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;

/**
 * Test Creation for EditSearchTermEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Product is created
 *
 * Steps:
 * 1. Go to frontend
 * 2. Test word into the Search field at the top of the page and click Go
 * 3. Go to backend as admin user
 * 4. Navigate to Marketing->SEO&Search->Search Terms
 * 5. Click "Edit" link of just added test word search term
 * 6. Fill out all data according to dataset
 * 7. Save the Search Term
 * 8. Perform all assertions
 *
 * @group Search_Terms_(MX)
 * @ZephyrId MAGETWO-26100
 */
class EditSearchTermEntityTest extends Injectable
{
    /**
     * CMS index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

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
     * Run edit search term test
     *
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function test(CatalogSearchQuery $searchTerm)
    {
        $this->markTestIncomplete('MAGETWO-26170');
        // Preconditions
        $searchText = $searchTerm->getQueryText();
        // Steps
        $this->cmsIndex->open()->getSearchBlock()->search($searchText);
        $this->indexPage->open()->getGrid()->searchAndOpen(['search_query' => $searchText]);
        $this->editPage->getForm()->fill($searchTerm);
        $this->editPage->getFormPageActions()->save();
    }
}
