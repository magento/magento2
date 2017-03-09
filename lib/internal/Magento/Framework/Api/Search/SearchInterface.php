<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Search API for all requests
 *
 * @api
 */
interface SearchInterface
{
    /**
     * Make Full Text Search and return found Documents
     *
     * @param \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function search(\Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria);
}
