<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
