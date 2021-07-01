<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Catalog\Frontend\ProductList\Price;

use Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;
use Magento\Elasticsearch\Model\Lucene;
use Magento\Elasticsearch\Model\Script\ScriptInterface;
use Magento\Elasticsearch\SearchAdapter\Field\ScriptResolverInterface;
use Magento\Framework\Search\Request;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class ScriptResolver implements ScriptResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Lucene\ScriptFactory
     */
    private $luceneScriptFactory;

    /**
     * @var Price\ExpressionResolverInterface
     */
    private $priceExpressionResolver;

    /**
     * @var LuceneExpressionBuilderFactory
     */
    private $priceExpressionBuilderFactory;

    /**
     * @var LuceneExpressionBuilder|null
     */
    private $priceExpressionBuilder;

    /**
     * @var Lucene\Expression\ExpressionInterface
     */
    private $requestPriceExpressions = [];

    /**
     * @param StoreManagerInterface $storeManager
     * @param Lucene\ScriptFactory $luceneScriptFactory
     * @param Price\ExpressionResolverInterface $priceExpressionResolver
     * @param LuceneExpressionBuilderFactory $priceExpressionBuilderFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Lucene\ScriptFactory $luceneScriptFactory,
        Price\ExpressionResolverInterface $priceExpressionResolver,
        LuceneExpressionBuilderFactory $priceExpressionBuilderFactory
    ) {
        $this->storeManager = $storeManager;
        $this->luceneScriptFactory = $luceneScriptFactory;
        $this->priceExpressionResolver = $priceExpressionResolver;
        $this->priceExpressionBuilderFactory = $priceExpressionBuilderFactory;
    }

    /**
     * @param Lucene\Expression\ExpressionInterface $expression
     * @return ScriptInterface
     */
    private function getExpressionScript(Lucene\Expression\ExpressionInterface $expression): ScriptInterface
    {
        return $this->luceneScriptFactory->create(
            [
                'rootExpression' => $expression,
            ]
        );
    }

    /**
     * @return LuceneExpressionBuilder
     */
    private function getPriceExpressionBuilder(): LuceneExpressionBuilder
    {
        if (null === $this->priceExpressionBuilder) {
            $this->priceExpressionBuilder = $this->priceExpressionBuilderFactory->create();
        }

        return $this->priceExpressionBuilder;
    }

    /**
     * @param string $requestName
     * @return Lucene\Expression\ExpressionInterface|null
     */
    private function getRequestPriceExpression(string $requestName): ?Lucene\Expression\ExpressionInterface
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $cacheKey = sprintf('%s-%d', $requestName, $storeId);

        if (!isset($this->requestPriceExpressions[$cacheKey])) {
            $priceExpressionBuilder = $this->getPriceExpressionBuilder();

            $this->requestPriceExpressions[$cacheKey] = $this->priceExpressionResolver->getPriceExpression(
                $priceExpressionBuilder->attributeValue('price'),
                RequestInterface::class,
                $requestName,
                $storeId,
                $priceExpressionBuilder
            );
        }

        return $this->requestPriceExpressions[$cacheKey];
    }

    public function getFieldAggregationScript(
        string $fieldName,
        ?Request\BucketInterface $bucket,
        string $requestName
    ): ?ScriptInterface {
        $priceExpression = $this->getRequestPriceExpression($requestName);

        return $priceExpression ? $this->getExpressionScript($priceExpression) : null;
    }

    public function getFieldFilterScript(
        string $fieldName,
        Request\FilterInterface $filter,
        string $requestName
    ): ?ScriptInterface {
        $priceExpression = null;

        if ($filter instanceof Request\Filter\Range) {
            $priceExpressionBuilder = $this->getPriceExpressionBuilder();
            $priceExpression = $this->getRequestPriceExpression($requestName);

            if (null !== $priceExpression) {
                $fromCondition = null;
                $toCondition = null;

                if ($bound = $filter->getFrom()) {
                    $fromCondition = $priceExpressionBuilder->greaterThanOrEqualTo(
                        $priceExpression,
                        $priceExpressionBuilder->double((float) $bound)
                    );
                }

                if ($bound = $filter->getTo()) {
                    $toCondition = $priceExpressionBuilder->lesserThanOrEqualTo(
                        $priceExpression,
                        $priceExpressionBuilder->double((float) $bound)
                    );
                }

                if (null !== $fromCondition) {
                    if (null !== $toCondition) {
                        $priceExpression = $priceExpressionBuilder->and($fromCondition, $toCondition);
                    } else {
                        $priceExpression = $fromCondition;
                    }
                } else {
                    $priceExpression = $toCondition;
                }
            }
        }

        return $priceExpression ? $this->getExpressionScript($priceExpression) : null;
    }

    public function getFieldSortScript(string $fieldName, string $direction, string $requestName): ?ScriptInterface
    {
        $priceExpression = $this->getRequestPriceExpression($requestName);

        return $priceExpression ? $this->getExpressionScript($priceExpression) : null;
    }
}
