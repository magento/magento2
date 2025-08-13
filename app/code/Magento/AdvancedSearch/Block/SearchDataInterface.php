<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Block;

/**
 * Interface \Magento\AdvancedSearch\Block\SearchDataInterface
 *
 * @api
 */
interface SearchDataInterface
{
    /**
     * Retrieve search suggestions
     *
     * @return array
     */
    public function getItems();

    /**
     * Check is need to show number of results
     *
     * @return bool
     */
    public function isShowResultsCount();

    /**
     * Retrieve link
     *
     * @param string $queryText
     * @return string
     */
    public function getLink($queryText);

    /**
     * Retrieve title
     *
     * @return string
     */
    public function getTitle();
}
