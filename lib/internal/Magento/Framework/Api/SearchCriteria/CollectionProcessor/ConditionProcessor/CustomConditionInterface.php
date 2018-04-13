<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Api\Filter;

/**
 * Interface CustomConditionInterface
 * Interface to build classes that can produce SQL conditions
 * that can be applied to entity collections to filter them by Magento\Framework\Api\Filter
 *
 * @package Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor
 */
interface CustomConditionInterface
{
    /**
     * @param Filter $filter
     * @return string
     */
    public function build(Filter $filter): string;
}
