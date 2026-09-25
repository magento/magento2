<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogGraphQl\Model\Category\ProductsCountProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Store\Model\Store;

/**
 * Retrieves products count for all categories requested in a single GraphQL request
 */
class BatchProductsCount implements BatchResolverInterface
{
    /**
     * @var ProductsCountProvider
     */
    private $productsCountProvider;

    /**
     * @var ProductsCount
     */
    private $productsCount;

    /**
     * @param ProductsCountProvider $productsCountProvider
     * @param ProductsCount $productsCount
     */
    public function __construct(ProductsCountProvider $productsCountProvider, ProductsCount $productsCount)
    {
        $this->productsCountProvider = $productsCountProvider;
        $this->productsCount = $productsCount;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $response = new BatchResponse();
        $groups = [];
        $assignments = [];

        /** @var BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            $value = $request->getValue();
            if (!isset($value['model'])) {
                throw new GraphQlInputException(__('"model" value should be specified'));
            }
            /** @var Category $category */
            $category = $value['model'];
            $storeId = (int)$category->getStoreId();
            if ($storeId === Store::DEFAULT_STORE_ID) {
                $response->addResponse(
                    $request,
                    $this->productsCount->resolve($field, $context, $request->getInfo(), $value, $request->getArgs())
                );
                continue;
            }
            $categoryId = (int)$category->getId();
            $isAnchor = (bool)$category->getIsAnchor();
            $groupKey = $storeId . '/' . (int)$isAnchor;
            $groups[$groupKey]['store_id'] = $storeId;
            $groups[$groupKey]['is_anchor'] = $isAnchor;
            $groups[$groupKey]['category_ids'][$categoryId] = $categoryId;
            $assignments[] = [$request, $groupKey, $categoryId];
        }

        $counts = [];
        foreach ($groups as $groupKey => $group) {
            $counts[$groupKey] = $this->productsCountProvider->getProductsCounts(
                $group['store_id'],
                array_values($group['category_ids']),
                $group['is_anchor']
            );
        }

        foreach ($assignments as [$request, $groupKey, $categoryId]) {
            $response->addResponse($request, $counts[$groupKey][$categoryId] ?? 0);
        }

        return $response;
    }
}
