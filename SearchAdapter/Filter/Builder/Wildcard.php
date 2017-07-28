<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Wildcard as WildcardFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

/**
 * Class \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard
 *
 * @since 2.1.0
 */
class Wildcard implements FilterInterface
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
     * @param RequestFilterInterface|WildcardFilterRequest $filter
     * @return array
     * @since 2.1.0
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        $fieldName = $this->fieldMapper->getFieldName($filter->getField());
        return [
            [
                'wildcard' => [
                    $fieldName => '*' . $filter->getValue() . '*',
                ],
            ]
        ];
    }
}
