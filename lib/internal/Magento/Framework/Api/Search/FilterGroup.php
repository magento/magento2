<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Groups two or more filters together using a logical OR
 *
 * @api
 */
class FilterGroup extends AbstractSimpleObject
{
    const FILTERS = 'filters';

    /**
     * Returns a list of filters in this group
     *
     * @return \Magento\Framework\Api\Filter[]|null
     */
    public function getFilters()
    {
        $filters = $this->_get(self::FILTERS);
        return $filters === null ? [] : $filters;
    }

    /**
     * Set filters
     *
     * @param \Magento\Framework\Api\Filter[] $filters
     * @return $this
     * @codeCoverageIgnore
     */
    public function setFilters(array $filters = null)
    {
        return $this->setData(self::FILTERS, $filters);
    }
}
