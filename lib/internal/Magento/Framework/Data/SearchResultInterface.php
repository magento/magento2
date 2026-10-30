<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data;

/**
 * Interface SearchResultInterface
 *
 * @api
 */
interface SearchResultInterface
{
    /**
     * Retrieve collection items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems();

    /**
     * Retrieve count of currently loaded items
     *
     * @return int
     */
    public function getTotalCount();

    /**
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function getSearchCriteria();
}
