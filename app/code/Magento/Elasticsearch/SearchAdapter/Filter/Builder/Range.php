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
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
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
     * Add the range filters
     *
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @return array
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        $filterQuery = [];
        $fieldName = $this->fieldMapper->getFieldName($filter->getField());
        if ($filter->getFrom() !== null) {
            $filterQuery['range'][$fieldName]['gte'] = $filter->getFrom();
        }
        if ($filter->getTo() !== null) {
            $filterQuery['range'][$fieldName]['lte'] = $filter->getTo();
        }
        return [$filterQuery];
    }
}
