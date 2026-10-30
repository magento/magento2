<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\SearchCriteria as BaseSearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * @api
 * @since 100.0.2
 */
class SearchCriteria extends BaseSearchCriteria implements SearchCriteriaInterface
{
    const REQUEST_NAME = 'request_name';

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
