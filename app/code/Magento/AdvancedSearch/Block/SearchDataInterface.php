<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block;

/**
 * Interface \Magento\AdvancedSearch\Block\SearchDataInterface
 *
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
