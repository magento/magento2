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
 * @since 2.0.0
 */
class FilterGroupBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var FilterBuilder
     * @since 2.0.0
     */
    protected $_filterBuilder;

    /**
     * @param ObjectFactory $objectFactory
     * @param FilterBuilder $filterBuilder
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setFilters(array $filters)
    {
        return $this->_set(FilterGroup::FILTERS, $filters);
    }
}
