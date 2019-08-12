<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use GraphQL\Language\AST\SelectionNode;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\Framework\GraphQl\Query\FieldTranslator;

/**
 * Retrieve filtered product data based off given search criteria in a format that GraphQL can interpret.
 */
class Filter
{
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product
     */
    private $productDataProvider;

    /**
     * @var FieldTranslator
     */
    private $fieldTranslator;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param Product $productDataProvider
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param FieldTranslator $fieldTranslator
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Product $productDataProvider,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        FieldTranslator $fieldTranslator
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->productDataProvider = $productDataProvider;
        $this->fieldTranslator = $fieldTranslator;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param ResolveInfo $info
     * @param bool $isSearch
     * @return SearchResult
     */
    public function getResult(
        SearchCriteriaInterface $searchCriteria,
        ResolveInfo $info,
        bool $isSearch = false
    ): SearchResult {
        $fields = $this->getProductFields($info);
        $products = $this->productDataProvider->getList($searchCriteria, $fields, $isSearch);
        $productArray = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products->getItems() as $product) {
            $productArray[$product->getId()] = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }

        return $this->searchResultFactory->create(
            [
                'totalCount' => $products->getTotalCount(),
                'productsSearchResult' => $productArray
            ]
        );
    }

    /**
     * Return field names for all requested product fields.
     *
     * @param ResolveInfo $info
     * @return string[]
     */
    private function getProductFields(ResolveInfo $info) : array
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            if ($node->name->value !== 'products') {
                continue;
            }
            foreach ($node->selectionSet->selections as $selection) {
                if ($selection->name->value !== 'items') {
                    continue;
                }
                $fieldNames[] = $this->collectProductFieldNames($selection, $fieldNames);
            }
        }

        $fieldNames = array_merge(...$fieldNames);

        return $fieldNames;
    }

    /**
     * Collect field names for each node in selection
     *
     * @param SelectionNode $selection
     * @param array $fieldNames
     * @return array
     */
    private function collectProductFieldNames(SelectionNode $selection, array $fieldNames = []): array
    {
        foreach ($selection->selectionSet->selections as $itemSelection) {
            if ($itemSelection->kind === 'InlineFragment') {
                foreach ($itemSelection->selectionSet->selections as $inlineSelection) {
                    if ($inlineSelection->kind === 'InlineFragment') {
                        continue;
                    }
                    $fieldNames[] = $this->fieldTranslator->translate($inlineSelection->name->value);
                }
                continue;
            }
            $fieldNames[] = $this->fieldTranslator->translate($itemSelection->name->value);
        }

        return $fieldNames;
    }
}
