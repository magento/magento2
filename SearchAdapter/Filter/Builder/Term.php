<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Term as TermFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

class Term implements FilterInterface
{
    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @param FieldMapperInterface $fieldMapper
     */
    public function __construct(FieldMapperInterface $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * @param RequestFilterInterface|TermFilterRequest $filter
     * @return array
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        $filterQuery = [];
        if ($filter->getValue()) {
            $filterValues =  is_array($filter->getValue()) ? $filter->getValue() : [$filter->getValue()];
            foreach ($filterValues as $value) {
                $filterQuery['or']['filters'][] = [
                    'term' => [
                        $this->fieldMapper->getFieldName($filter->getField()) => $value,
                    ],
                ];
            }
        }
        return $filterQuery;
    }
}
