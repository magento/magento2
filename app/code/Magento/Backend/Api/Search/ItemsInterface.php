<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Api\Search;

use Magento\Backend\Model\Search\SearchCriteria;

/**
 * @api
 */
interface ItemsInterface
{
    /**
     * Get the search result items
     *
     * @param SearchCriteria $searchCriteria
     * @return array
     */
    public function getResults(SearchCriteria $searchCriteria);
}
