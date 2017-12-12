<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\GraphQl\Model\Type\Handler\SearchResultPageInfo;

/**
 * Define GraphQL type for search result of Products
 */
class Products implements HandlerInterface
{
    const PRODUCTS_TYPE_NAME = 'Products';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeFactory $typeFactory
     */
    public function __construct(Pool $typePool, TypeFactory $typeFactory)
    {
        $this->typePool = $typePool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->typeFactory->createObject(
            [
                'name' => self::PRODUCTS_TYPE_NAME,
                'fields' => $this->getFields(),
            ]
        );
    }

    /**
     * Retrieve the result fields
     *
     * @return array
     */
    private function getFields()
    {
        $fields = [
            'items' => $this->typeFactory->createList($this->typePool->getType(Product::PRODUCT_TYPE_NAME)),
            'page_info' => $this->typePool->getType(SearchResultPageInfo::SEARCH_RESULT_PAGE_INFO_TYPE_NAME),
            'total_count' => $this->typePool->getType('Int')
        ];
        return $fields;
    }
}
