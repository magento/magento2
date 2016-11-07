<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Block\Advanced;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block for search results page title.
 */
class SearchResultsTitle extends Block
{
    /**
     * CSS selector for block 'Search results for'.
     *
     * @var string
     */
    protected $searchResultsFor = '[data-ui-id="page-title-wrapper"]';

    /**
     * Getting actual search query value.
     *
     * @return string
     */
    public function searchQueryValue()
    {
        $searchQueryResult = $this->_rootElement->find(sprintf($this->searchResultsFor), Locator::SELECTOR_CSS)
            ->getText();
        preg_match("~Search results for: \'(.*)\'~", $searchQueryResult, $matches);
        $query = isset($matches[1]) ? $matches[1] : null;
        return $query;
    }

    /**
     * Getting length of search query.
     *
     * @return int
     */
    public function searchQueryLength()
    {
        return mb_strlen($this->searchQueryValue());
    }
}
