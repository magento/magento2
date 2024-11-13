<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
