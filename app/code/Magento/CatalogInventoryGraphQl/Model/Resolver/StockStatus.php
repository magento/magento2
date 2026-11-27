<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\StockRegistryPreloader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Resolve the stock status of all products of a single response with one stock status query.
 */
class StockStatus implements BatchResolverInterface
{
    /**
     * In Stock return code
     */
    private const IN_STOCK = "IN_STOCK";

    /**
     * Out of Stock return code
     */
    private const OUT_OF_STOCK = "OUT_OF_STOCK";

    /**
     * @param StockRegistryPreloader $stockRegistryPreloader
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockStatusProvider $stockStatusProvider
     */
    public function __construct(
        private readonly StockRegistryPreloader $stockRegistryPreloader,
        private readonly StockConfigurationInterface $stockConfiguration,
        private readonly StockStatusProvider $stockStatusProvider
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $productIds = [];
        foreach ($requests as $request) {
            if (!$this->isCartItemRequest($request)) {
                $productIds[] = (int)$this->getProduct($request)->getId();
            }
        }

        $stockStatuses = [];
        if ($productIds) {
            $preloaded = $this->stockRegistryPreloader->preloadStockStatuses(
                array_values(array_unique($productIds)),
                (int)$this->stockConfiguration->getDefaultScopeId()
            );
            foreach ($preloaded as $stockStatus) {
                $stockStatuses[(int)$stockStatus->getProductId()] = (int)$stockStatus->getStockStatus();
            }
        }

        $response = new BatchResponse();
        foreach ($requests as $request) {
            if ($this->isCartItemRequest($request)) {
                $response->addResponse(
                    $request,
                    $this->stockStatusProvider->resolve(
                        $field,
                        $context,
                        $request->getInfo(),
                        $request->getValue(),
                        $request->getArgs()
                    )
                );
                continue;
            }

            $productId = (int)$this->getProduct($request)->getId();
            $response->addResponse(
                $request,
                empty($stockStatuses[$productId]) ? self::OUT_OF_STOCK : self::IN_STOCK
            );
        }

        return $response;
    }

    /**
     * Get the product a request has been made for
     *
     * @param BatchRequestItemInterface $request
     * @return ProductInterface
     * @throws LocalizedException
     */
    private function getProduct(BatchRequestItemInterface $request): ProductInterface
    {
        $value = $request->getValue() ?? [];
        if (!array_key_exists('model', $value) || !$value['model'] instanceof ProductInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        return $value['model'];
    }

    /**
     * Cart items keep their own stock status semantics and are delegated to the single-item resolver
     *
     * @param BatchRequestItemInterface $request
     * @return bool
     * @throws LocalizedException
     */
    private function isCartItemRequest(BatchRequestItemInterface $request): bool
    {
        $this->getProduct($request);

        return ($request->getValue()['cart_item'] ?? null) instanceof Item;
    }
}
