<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;


use Magento\Framework\Api\SearchResults;
use Magento\Sitemap\Api\Data\SitemapSearchResultsInterface;

/**
 * Class SitemapSearchResults
 * @package Magento\Sitemap\Model
 */
class SitemapSearchResults extends SearchResults implements SitemapSearchResultsInterface
{

}