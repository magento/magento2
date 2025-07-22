<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\RelatedProductGraphQl\Model\Resolver\Batch;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\RelatedProductGraphQl\Model\DataProvider\RelatedProductDataProvider;
use Magento\RelatedProductGraphQl\Model\ResourceModel\RelatedProductsByStoreId;

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
     * @var RelatedProductsByStoreId
     */
    private $relatedProductsByStoreId;

    /**
     * @param ProductFieldsSelector $productFieldsSelector
     * @param RelatedProductDataProvider $relatedProductDataProvider
     * @param ProductDataProvider $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RelatedProductsByStoreId|null $relatedProductsByStoreId
     */
    public function __construct(
        ProductFieldsSelector $productFieldsSelector,
        RelatedProductDataProvider $relatedProductDataProvider,
        ProductDataProvider $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ?RelatedProductsByStoreId $relatedProductsByStoreId = null
    ) {
        $this->productFieldsSelector = $productFieldsSelector;
        $this->relatedProductDataProvider = $relatedProductDataProvider;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->relatedProductsByStoreId = $relatedProductsByStoreId ??
            ObjectManager::getInstance()->get(RelatedProductsByStoreId::class);
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
     * @param ProductInterface[] $products
     * @param string[] $loadAttributes
     * @param int $linkType
     * @param string $websiteId
     * @return ProductInterface[][]
     * @throws LocalizedException
     */
    private function findRelations(
        array $products,
        array $loadAttributes,
        int $linkType,
        string $websiteId
    ): array {
        //Loading relations
        $relations = $this->relatedProductDataProvider->getRelations($products, $linkType);
        if (!$relations) {
            return [];
        }
        //get related product ids by website id
        $relatedIds = $this->relatedProductsByStoreId->execute(
            array_unique(array_merge([], ...array_values($relations))),
            $websiteId
        );
        //Loading products data.
        $this->searchCriteriaBuilder->addFilter('entity_id', $relatedIds, 'in');
        $relatedSearchResult = $this->productDataProvider->getList(
            $this->searchCriteriaBuilder->create(),
            $loadAttributes
        );
        //Filling related products map.
        /** @var ProductInterface[] $relatedProducts */
        $relatedProducts = [];
        /** @var ProductInterface $item */
        foreach ($relatedSearchResult->getItems() as $item) {
            if ($item->isAvailable()) {
                $relatedProducts[$item->getId()] = $item;
            }
        }

        //Matching products with related products.
        $relationsData = [];
        foreach ($relations as $productId => $relatedIds) {
            //Remove related products that not exist in map list.
            $relatedIds = array_filter($relatedIds, function ($relatedId) use ($relatedProducts) {
                return isset($relatedProducts[$relatedId]);
            });
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
        /** @var ProductInterface[] $products */
        $products = [];
        $fields = [];
        /** @var BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            //Gathering fields and relations to load.
            if (empty($request->getValue()['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            $products[] = $request->getValue()['model'];
            $fields[] = $this->productFieldsSelector->getProductFieldsFromInfo($request->getInfo(), $this->getNode());
        }
        $fields = array_unique(array_merge([], ...$fields));

        $store = $context->getExtensionAttributes()->getStore();
        $websiteId = $store->getWebsiteId();
        //Finding relations.
        $related = $this->findRelations($products, $fields, $this->getLinkType(), (string) $websiteId);

        //Matching requests with responses.
        $response = new BatchResponse();
        /** @var BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            /** @var ProductInterface $product */
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
