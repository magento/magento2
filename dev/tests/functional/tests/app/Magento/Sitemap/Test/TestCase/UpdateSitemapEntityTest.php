<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Cover updating Sitemap Entity
 *
 * Test Flow:
 * Preconditions:
 *  1. Generate sitemap
 *  2. Create category
 *  3. Create simple product
 *  4. Create CMS page
 * Steps:
 *  1. Login as Admin User
 *  2. Go to Marketing > SEO & Search: Site Map
 *  3. Click 'Generate' In the grid for sitemap from preconditions
 *  4. Perform all assertions
 *
 * @group XML_Sitemap
 * @ZephyrId MAGETWO-25362
 */
class UpdateSitemapEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Sitemap grid page
     *
     * @var SitemapIndex
     */
    protected $sitemapIndex;

    /**
     * Inject data
     *
     * @param SitemapIndex $sitemapIndex
     * @return void
     */
    public function __inject(SitemapIndex $sitemapIndex)
    {
        $this->sitemapIndex = $sitemapIndex;
    }

    /**
     * Update Sitemap Entity
     *
     * @param Sitemap $sitemap
     * @param CatalogProductSimple $product
     * @param Category $catalog
     * @param CmsPage $cmsPage
     * @return void
     */
    public function testUpdateSitemap(
        Sitemap $sitemap,
        CatalogProductSimple $product,
        Category $catalog,
        CmsPage $cmsPage
    ) {
        // Preconditions
        $sitemap->persist();
        $product->persist();
        $catalog->persist();
        $cmsPage->persist();
        $filter = [
            'sitemap_filename' => $sitemap->getSitemapFilename(),
            'sitemap_path' => $sitemap->getSitemapPath(),
            'sitemap_id' => $sitemap->getSitemapId(),
        ];

        // Steps
        $this->sitemapIndex->open()->getSitemapGrid()->search($filter);
        $this->sitemapIndex->getSitemapGrid()->generate();
    }
}
