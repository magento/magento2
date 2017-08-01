<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultInterface
 * @since 2.0.0
 */
interface SearchResultInterface
{
    /**
     * Retrieve collection items
     *
     * @return \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Retrieve count of currently loaded items
     *
     * @return int
     * @since 2.0.0
     */
    public function getTotalCount();

    /**
     * @return \Magento\Framework\Api\CriteriaInterface
     * @since 2.0.0
     */
    public function getSearchCriteria();
}
