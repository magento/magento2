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
 * @since 2.0.0
 */
interface SearchCriteriaInterface extends BaseSearchCriteriaInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getRequestName();

    /**
     * @param string $requestName
     * @return $this
     * @since 2.0.0
     */
    public function setRequestName($requestName);
}
