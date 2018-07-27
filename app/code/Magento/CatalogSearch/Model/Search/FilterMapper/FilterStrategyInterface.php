<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

/**
 * FilterStrategyInterface provides the interface to work with strategies.
 */
interface FilterStrategyInterface
{
    /**
     * Applies filter.
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     *
     * @return bool is filter was applied
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    );
}
