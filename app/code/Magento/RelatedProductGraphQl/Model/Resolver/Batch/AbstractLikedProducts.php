<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RelatedProductGraphQl\Model\Resolver\Batch;

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\RelatedProductGraphQl\Model\DataProvider\RelatedProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Resolve linked product lists.
 */
abstract class AbstractLikedProducts implements BatchResolverInterface
{
    /**
     * @var ProductFieldsSelector
     */
    private $productFieldsSelector;

    /**
     * @var RelatedProductDataProvider
     */
    private $relatedProductDataProvider;

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductFieldsSelector $productFieldsSelector
     * @param RelatedProductDataProvider $relatedProductDataProvider
     * @param ProductDataProvider $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductFieldsSelector $productFieldsSelector,
        RelatedProductDataProvider $relatedProductDataProvider,
        ProductDataProvider $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productFieldsSelector = $productFieldsSelector;
        $this->relatedProductDataProvider = $relatedProductDataProvider;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Node type.
     *
     * @return string
     */
    abstract protected function getNode(): string;

    /**
     * Type of linked products to be resolved.
     *
     * @return int
     */
    abstract protected function getLinkType(): int;

    /**
     * Find related products.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @param string[] $loadAttributes
     * @param int $linkType
     * @return \Magento\Catalog\Api\Data\ProductInterface[][]
     */
    private function findRelations(array $products, array $loadAttributes, int $linkType): array
    {
        //Loading relations
        $relations = $this->relatedProductDataProvider->getRelations($products, $linkType);
        if (!$relations) {
            return [];
        }
        $relatedIds = array_values($relations);
        $relatedIds = array_unique(array_merge(...$relatedIds));
        //Loading products data.
        $this->searchCriteriaBuilder->addFilter('entity_id', $relatedIds, 'in');
        $relatedSearchResult = $this->productDataProvider->getList(
            $this->searchCriteriaBuilder->create(),
            $loadAttributes,
            false,
            true
        );
        //Filling related products map.
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $relatedProducts */
        $relatedProducts = [];
        /** @var \Magento\Catalog\Api\Data\ProductInterface $item */
        foreach ($relatedSearchResult->getItems() as $item) {
            $relatedProducts[$item->getId()] = $item;
        }

        //Matching products with related products.
        $relationsData = [];
        foreach ($relations as $productId => $relatedIds) {
            $relationsData[$productId] = array_map(
                function ($id) use ($relatedProducts) {
                    return $relatedProducts[$id];
                },
                $relatedIds
            );
        }

        return $relationsData;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $products */
        $products = [];
        $fields = [];
        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            //Gathering fields and relations to load.
            if (empty($request->getValue()['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            $products[] = $request->getValue()['model'];
            $fields[] = $this->productFieldsSelector->getProductFieldsFromInfo($request->getInfo(), $this->getNode());
        }
        $fields = array_unique(array_merge(...$fields));

        //Finding relations.
        $related = $this->findRelations($products, $fields, $this->getLinkType());

        //Matching requests with responses.
        $response = new BatchResponse();
        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $request->getValue()['model'];
            $result = [];
            if (array_key_exists($product->getId(), $related)) {
                $result = array_map(
                    function ($relatedProduct) {
                        $data = $relatedProduct->getData();
                        $data['model'] = $relatedProduct;

                        return $data;
                    },
                    $related[$product->getId()]
                );
            }
            $response->addResponse($request, $result);
        }

        return $response;
    }
}
