<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\SearchCriteriaInterface as BaseSearchCriteriaInterface;

/**
 * Interface SearchCriteriaInterface
 *
 * @api
 * @package Magento\Framework\Api\Search
 * @since 100.0.2
 */
interface SearchCriteriaInterface extends BaseSearchCriteriaInterface
{
    /**
     * @return string
     */
    public function getRequestName();

    /**
     * @param string $requestName
     * @return $this
     */
    public function setRequestName($requestName);
}
