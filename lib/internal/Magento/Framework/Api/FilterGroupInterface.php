<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface FilterGroupInterface
 * @package Magento\Framework\Api
 */
interface FilterGroupInterface
{
    /**
     * @return \Magento\Framework\Api\Filter[]|null
     */
    public function getFilters();

    /**
     * @param \Magento\Framework\Api\Filter[]|null $filters
     * @return $this
     */
    public function setFilters(array $filters = null);
}
