<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Term as TermFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

/**
 * Class \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term
 *
 * @since 2.1.0
 */
class Term implements FilterInterface
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
    public function __construct(FieldMapperInterface $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * @param RequestFilterInterface|TermFilterRequest $filter
     * @return array
     * @since 2.1.0
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
