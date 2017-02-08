<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\TestCase;

use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapEdit;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Mtf\TestCase\Injectable;

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
 * @group XML_Sitemap
 * @ZephyrId MAGETWO-23296
 */
class DeleteSitemapEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

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
            'sitemap_id' => $sitemap->getSitemapId(),
        ];
        // Steps
        $this->sitemapIndex->open();
        $this->sitemapIndex->getSitemapGrid()->searchAndOpen($filter);
        $this->sitemapEdit->getFormPageActions()->delete();
        $this->sitemapEdit->getModalBlock()->acceptAlert();
    }
}
