<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block;

/**
 * Interface \Magento\AdvancedSearch\Block\SearchDataInterface
 *
 * @since 2.0.0
 */
interface SearchDataInterface
{
    /**
     * Retrieve search suggestions
     *
     * @return array
     * @since 2.0.0
     */
    public function getItems();

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isShowResultsCount();

    /**
     * @param string $queryText
     * @return string
     * @since 2.0.0
     */
    public function getLink($queryText);

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTitle();
}
