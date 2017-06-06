<?php

namespace Liip\CustomerHierarchy\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface RoleManagerInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @return int
     */
    public function save();

    /**
     * @param int $id
     * @return \Liip\CustomerHierarchy\Api\Data\RoleInterface
     */
    public function get($id);
}
