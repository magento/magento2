<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\ObjectFactory;

/**
 * Builder for FilterGroup Data.
 *
 * @api
 * @since 100.0.2
 */
class FilterGroupBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * @param ObjectFactory $objectFactory
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        FilterBuilder $filterBuilder
    ) {
        parent::__construct(
            $objectFactory
        );
        $this->_filterBuilder = $filterBuilder;
    }

    /**
     * Add filter
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->data[FilterGroup::FILTERS][] = $filter;
        return $this;
    }

    /**
     * Set filters
     *
     * @param \Magento\Framework\Api\Filter[] $filters
     * @return $this
     */
    public function setFilters(array $filters)
    {
        return $this->_set(FilterGroup::FILTERS, $filters);
    }
}
