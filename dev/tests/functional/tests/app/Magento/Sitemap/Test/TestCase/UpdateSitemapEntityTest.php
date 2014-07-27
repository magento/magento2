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

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Sitemap\Test\Fixture\Sitemap;
use Mtf\TestCase\Injectable;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;

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
 * @group XML_Sitemap_(PS)
 * @ZephyrId MAGETWO-25362
 */
class UpdateSitemapEntityTest extends Injectable
{
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
     * @param CatalogCategory $catalog
     * @param CmsPage $cmsPage
     * @return void
     */
    public function testUpdateSitemap(
        Sitemap $sitemap,
        CatalogProductSimple $product,
        CatalogCategory $catalog,
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
            'sitemap_id' => $sitemap->getSitemapId()
        ];

        // Steps
        $this->sitemapIndex->open()->getSitemapGrid()->search($filter);
        $this->sitemapIndex->getSitemapGrid()->generate();
    }
}
