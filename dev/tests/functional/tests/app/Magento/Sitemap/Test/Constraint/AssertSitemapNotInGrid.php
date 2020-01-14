<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Constraint;

use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSitemapNotInGrid
 */
class AssertSitemapNotInGrid extends AbstractConstraint
{
    /**
     * Assert that sitemap not availability in sitemap grid
     *
     * @param Sitemap $sitemap
     * @param SitemapIndex $sitemapPageGrid
     * @return void
     */
    public function processAssert(Sitemap $sitemap, SitemapIndex $sitemapPageGrid)
    {
        $sitemapPageGrid->open();
        $filter = [
            'sitemap_filename' => $sitemap->getSitemapFilename(),
            'sitemap_path' => $sitemap->getSitemapPath(),
            'sitemap_id' => $sitemap->getSitemapId(),
        ];
        \PHPUnit\Framework\Assert::assertFalse(
            $sitemapPageGrid->getSitemapGrid()->isRowVisible($filter),
            'Sitemap with filename \'' . $sitemap->getSitemapFilename() . '\' and id \''
            . $sitemap->getSitemapId() . '\' and path \''
            . $sitemap->getSitemapPath() . '\' is present in Sitemap grid.'
        );
    }

    /**
     * Text of absence sitemap in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sitemap in grid is absent.';
    }
}
