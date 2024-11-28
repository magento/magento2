<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\ExpressionBuilderInterface as SortExpressionBuilder;
use Magento\Framework\Search\RequestInterface;

/**
 * Sort builder.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class Sort
{
    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var SortExpressionBuilder
     */
    private $sortExpressionBuilder;

    /**
     * @param AttributeProvider $attributeAdapterProvider
     * @param SortExpressionBuilder $sortExpressionBuilder
     */
    public function __construct(
        AttributeProvider $attributeAdapterProvider,
        SortExpressionBuilder $sortExpressionBuilder
    ) {
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->sortExpressionBuilder = $sortExpressionBuilder;
    }

    /**
     * Prepare sort.
     *
     * @param RequestInterface $request
     * @return array
     */
    public function getSort(RequestInterface $request)
    {
        $sorts = [];
        /**
         * Temporary solution for an existing interface of a fulltext search request in Backward compatibility purposes.
         * Scope to split Search request interface on two different 'Search' and 'Fulltext Search' contains in MC-16461.
         */
        if (!method_exists($request, 'getSort')) {
            return $sorts;
        }

        foreach ($request->getSort() as $item) {
            $attribute = $this->attributeAdapterProvider->getByAttributeCode((string)$item['field']);
            $direction = strtolower($item['direction'] ?? '');
            $sorts[] = $this->sortExpressionBuilder->build($attribute, $direction, $request);
        }

        return $sorts;
    }
}
