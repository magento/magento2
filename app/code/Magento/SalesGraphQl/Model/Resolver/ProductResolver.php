<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderItemInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetches the Product data according to the GraphQL schema
 */
class ProductResolver implements ResolverInterface
{
    /**
     * ProductResolver Constructor
     *
     * @param ProductDataProvider $productDataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ProductDataProvider $productDataProvider,
        private readonly LoggerInterface     $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): ?array {
        $orderItem = $value['model'] ?? null;
        if (!$orderItem instanceof OrderItemInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        try {
            return $this->productDataProvider->getProductDataById((int)$orderItem->getProductId());
        } catch (NoSuchEntityException | GraphQlNoSuchEntityException $exception) {
            $this->logger->error(
                sprintf('ProductResolver: Product not found. Exception: %s', $exception->getMessage()),
                ['exception' => $exception, 'arguments' => $args]
            );

            return null;
        }
    }
}
