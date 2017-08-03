<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

/**
 * Class \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range
 *
 * @since 2.1.0
 */
class Range implements FilterInterface
{
    /**
     * @var FieldMapperInterface
     * @since 2.1.0
     */
    protected $fieldMapper;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @since 2.1.0
     */
    public function __construct(
        FieldMapperInterface $fieldMapper
    ) {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @return array
     * @since 2.1.0
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        $filterQuery = [];
        $fieldName = $this->fieldMapper->getFieldName($filter->getField());
        if ($filter->getFrom()) {
            $filterQuery['range'][$fieldName]['gte'] = $filter->getFrom();
        }
        if ($filter->getTo()) {
            $filterQuery['range'][$fieldName]['lte'] = $filter->getTo();
        }
        return [$filterQuery];
    }
}
