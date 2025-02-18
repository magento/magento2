<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogInventoryGraphQl\Model\StockItemService;

/**
 * Resolver for ProductInterface min quantity
 * Returns the available stock min quantity
 */
class MinSaleQtyResolver implements ResolverInterface
{
    /**
     * @var StockItemService
     */
    private $stockItemService;

    /**
     * @param StockItemService $stockItemService
     */
    public function __construct(
        StockItemService $stockItemService
    ) {
        $this->stockItemService = $stockItemService;
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
    ) {
        $stockItem = $this->stockItemService->getStockItem($value['model']);
        return $stockItem?->getMinSaleQty();
    }
}
