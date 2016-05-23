<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultInterface
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
