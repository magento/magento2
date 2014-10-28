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

namespace Magento\Sitemap\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;

/**
 * Class AssertSitemapInGrid
 */
class AssertSitemapInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that sitemap availability in sitemap grid
     *
     * @param Sitemap $sitemap
     * @param SitemapIndex $sitemapIndex
     * @return void
     */
    public function processAssert(Sitemap $sitemap, SitemapIndex $sitemapIndex)
    {
        $sitemapIndex->open()->getSitemapGrid()->sortGridByField('sitemap_id');
        $filter = [
            'sitemap_filename' => $sitemap->getSitemapFilename(),
            'sitemap_path' => $sitemap->getSitemapPath(),
        ];
        \PHPUnit_Framework_Assert::assertTrue(
            $sitemapIndex->getSitemapGrid()->isRowVisible($filter),
            'Sitemap with filename \'' . $sitemap->getSitemapFilename() . '\' and path \''
            . $sitemap->getSitemapPath() . '\' is absent in Sitemap grid. \''
        );
    }

    /**
     * Text of presence sitemap in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sitemap in grid is present.';
    }
}
