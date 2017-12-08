<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define SearchCriteriaExpression GraphQL type
 */
class SearchCriteriaExpression implements HandlerInterface
{
    const SEARCH_CRITERIA_EXPRESSION_TYPE_NAME = 'SearchCriteriaExpression';

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $pool
     * @param TypeFactory $typeFactory
     */
    public function __construct(Pool $pool, TypeFactory $typeFactory)
    {
        $this->pool = $pool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->typeFactory->createInputObject(
            [
                'name' => self::SEARCH_CRITERIA_EXPRESSION_TYPE_NAME,
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Retrieve fields
     *
     * @return array
     */
    private function getFields()
    {
        $stringType = $this->pool->getType('String');
        $stringListType = $this->typeFactory->createList($stringType);
        
        $fields = [
            'eq' => $stringType,
            'finset' => $stringListType,
            'from' => $stringType,
            'gt' => $stringType,
            'gteq' => $stringType,
            'in' => $stringListType,
            'like' => $stringType,
            'lt' => $stringType,
            'lteq' => $stringType,
            'moreq' => $stringType,
            'neq' => $stringType,
            'nin' => $stringListType,
            'notnull' => $stringType,
            'null' => $stringType,
            'to' => $stringType,
        ];

        return $fields;
    }
}
