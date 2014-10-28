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

namespace Magento\Sitemap\Test\TestCase;

use Magento\Sitemap\Test\Fixture\Sitemap;
use Mtf\TestCase\Injectable;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapEdit;

/**
 * Cover deleting Sitemap Entity
 *
 * Test Flow:
 * Preconditions:
 *  1. Create new sitemap.
 * Steps:
 *  1. Log in as admin user from data set.
 *  2. Navigate to Marketing > SEO and Search > Site Map.
 *  3. Open sitemap from precondition.
 *  4. Click "Delete" button.
 *  5. Perform all assertions.
 *
 * @group XML_Sitemap_(MX)
 * @ZephyrId MAGETWO-23296
 */
class DeleteSitemapEntityTest extends Injectable
{
    /**
     * @var SitemapIndex
     */
    protected $sitemapIndex;

    /**
     * @var SitemapEdit
     */
    protected $sitemapEdit;

    /**
     * @param SitemapIndex $sitemapIndex
     * @param SitemapEdit $sitemapEdit
     */
    public function __inject(
        SitemapIndex $sitemapIndex,
        SitemapEdit $sitemapEdit
    ) {
        $this->sitemapIndex = $sitemapIndex;
        $this->sitemapEdit = $sitemapEdit;
    }

    /**
     * @param Sitemap $sitemap
     */
    public function testDeleteSitemap(Sitemap $sitemap)
    {
        // Preconditions
        $sitemap->persist();
        $filter = [
            'sitemap_filename' => $sitemap->getSitemapFilename(),
            'sitemap_path' => $sitemap->getSitemapPath(),
            'sitemap_id' => $sitemap->getSitemapId()
        ];
        // Steps
        $this->sitemapIndex->open();
        $this->sitemapIndex->getSitemapGrid()->searchAndOpen($filter);
        $this->sitemapEdit->getFormPageActions()->delete();
    }
}
