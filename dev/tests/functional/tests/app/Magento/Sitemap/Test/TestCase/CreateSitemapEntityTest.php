<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\TestCase;

use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Cover creating SitemapEntity
 *
 * Test Flow:
 *  1. Log in as admin user from data set.
 *  2. Navigate to Marketing > SEO and Search > Site Map.
 *  3. Click "Add Sitemap" button.
 *  4. Fill out all data according to data set.
 *  5. Click "Save" button.
 *  6. Perform all assertions.
 *
 * @group XML_Sitemap
 * @ZephyrId MAGETWO-23277
 */
class CreateSitemapEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * @var SitemapIndex
     */
    protected $sitemapIndex;

    /**
     * @var SitemapNew
     */
    protected $sitemapNew;

    /**
     * @param SitemapIndex $sitemapIndex
     * @param SitemapNew $sitemapNew
     */
    public function __inject(
        SitemapIndex $sitemapIndex,
        SitemapNew $sitemapNew
    ) {
        $this->sitemapIndex = $sitemapIndex;
        $this->sitemapNew = $sitemapNew;
    }

    /**
     * @param Sitemap $sitemap
     */
    public function testCreateSitemap(Sitemap $sitemap)
    {
        // Steps
        $this->sitemapIndex->open();
        $this->sitemapIndex->getGridPageActions()->addNew();
        $this->sitemapNew->getSitemapForm()->fill($sitemap);
        $this->sitemapNew->getSitemapPageActions()->save();
    }
}
