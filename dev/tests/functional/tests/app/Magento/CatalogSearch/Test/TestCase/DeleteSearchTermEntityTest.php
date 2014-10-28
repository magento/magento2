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
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;

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
 * @group Search_Terms_(MX)
 * @ZephyrId MAGETWO-26491
 */
class DeleteSearchTermEntityTest extends Injectable
{
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
    }
}
