<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

class Range implements FilterInterface
{
    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @param FieldMapperInterface $fieldMapper
     */
    public function __construct(
        FieldMapperInterface $fieldMapper
    ) {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @return array
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
