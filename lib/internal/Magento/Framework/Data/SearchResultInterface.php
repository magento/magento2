<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @return \Magento\Framework\Object[]
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
