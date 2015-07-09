<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\SearchCriteria as BaseSearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * Class SearchCriteria
 */
class SearchCriteria extends BaseSearchCriteria implements SearchCriteriaInterface
{
    const SEARCH_TERM = 'search_term';
    const REQUEST_NAME = 'request_name';
    /**
     * {@inheritdoc}
     */
    public function getSearchTerm()
    {
        return $this->_get(self::SEARCH_TERM);
    }
    /**
     * {@inheritdoc}
     */
    public function setSearchTerm($searchTerm)
    {
        return $this->setData(self::SEARCH_TERM, $searchTerm);
    }
    /**
     * {@inheritdoc}
     */
    public function getRequestName()
    {
        return $this->_get(self::REQUEST_NAME);
    }
    /**
     * {@inheritdoc}
     */
    public function setRequestName($requestName)
    {
        return $this->setData(self::REQUEST_NAME, $requestName);
    }
}
