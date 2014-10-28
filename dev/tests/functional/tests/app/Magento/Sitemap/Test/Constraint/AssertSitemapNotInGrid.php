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
 * Class AssertSitemapNotInGrid
 */
class AssertSitemapNotInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
            'sitemap_id' => $sitemap->getSitemapId()
        ];
        \PHPUnit_Framework_Assert::assertFalse(
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
