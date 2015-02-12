<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block;

interface SearchDataInterface
{
    /**
     * Retrieve search suggestions
     *
     * @return array
     */
    public function getSearchData();

    /**
     * @return bool
     */
    public function isShowResultsCount();

    /**
     * @param string $queryText
     * @return string
     */
    public function getLink($queryText);

    /**
     * @return string
     */
    public function getTitle();
}
