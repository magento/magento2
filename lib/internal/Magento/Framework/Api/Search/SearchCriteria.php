<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\SearchCriteria as BaseSearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * @api
 * @since 2.0.0
 */
class SearchCriteria extends BaseSearchCriteria implements SearchCriteriaInterface
{
    const REQUEST_NAME = 'request_name';

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRequestName()
    {
        return $this->_get(self::REQUEST_NAME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRequestName($requestName)
    {
        return $this->setData(self::REQUEST_NAME, $requestName);
    }
}
