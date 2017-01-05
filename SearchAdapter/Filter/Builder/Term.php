<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            $operator = is_array($filter->getValue()) ? 'terms' : 'term';
            $filterQuery []= [
                $operator => [
                    $this->fieldMapper->getFieldName($filter->getField()) => $filter->getValue(),
                ],
            ];
        }
        return $filterQuery;
    }
}
