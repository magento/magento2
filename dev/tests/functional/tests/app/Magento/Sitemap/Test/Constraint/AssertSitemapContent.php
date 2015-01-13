<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSitemapContent
 * Assert that sitemap.xml file contains correct content
 */
class AssertSitemapContent extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that sitemap.xml file contains correct content according to dataset:
     *  - product url
     *  - category url
     *  - CMS page url
     *
     * @param CatalogProductSimple $product
     * @param CatalogCategory $catalog
     * @param CmsPage $cmsPage
     * @param Sitemap $sitemap
     * @param SitemapIndex $sitemapIndex
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        CatalogCategory $catalog,
        CmsPage $cmsPage,
        Sitemap $sitemap,
        SitemapIndex $sitemapIndex
    ) {
        $sitemapIndex->open()->getSitemapGrid()->sortGridByField('sitemap_id');
        $filter = [
            'sitemap_filename' => $sitemap->getSitemapFilename(),
            'sitemap_path' => $sitemap->getSitemapPath(),
        ];
        $sitemapIndex->getSitemapGrid()->search($filter);
        $content = file_get_contents($sitemapIndex->getSitemapGrid()->getLinkForGoogle());
        $urls = [
            $_ENV['app_frontend_url'] . $product->getUrlKey() . '.html',
            $_ENV['app_frontend_url'] . $catalog->getUrlKey() . '.html',
            $_ENV['app_frontend_url'] . $cmsPage->getIdentifier(),
        ];

        \PHPUnit_Framework_Assert::assertTrue(
            $this->checkContent($content, $urls),
            'Content of file sitemap.xml does not include one or more of next urls:'
            . implode("\n", $urls)
        );
    }

    /**
     * Check content for the presence urls
     *
     * @param string $content
     * @param array $urls
     * @return bool
     */
    protected function checkContent($content, $urls)
    {
        foreach ($urls as $url) {
            if (strpos($content, $url) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'File sitemap.xml contains correct content according to dataset.';
    }
}
