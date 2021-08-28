<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\IsProductSalableConditionChain;
use Psr\Log\LoggerInterface;

/**
 * Lightweight implementation for Storefront application.
 */
class IsProductSalable implements IsProductSalableInterface
{
    /**
     * @var IsProductSalableConditionChain
     */
    private $isProductSalableConditionChain;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param IsProductSalableConditionChain $getStockItemData
     * @param LoggerInterface $logger
     */
    public function __construct(
        IsProductSalableConditionChain $isProductSalableConditionChain,
        LoggerInterface                $logger
    )
    {
        $this->isProductSalableConditionChain = $isProductSalableConditionChain;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        try {
            $isSalable = $this->isProductSalableConditionChain->execute($sku, $stockId);
        } catch (LocalizedException $exception) {
            $this->logger->warning(
                sprintf(
                    'Unable to fetch stock #%s data for SKU %s. Reason: %s',
                    $stockId,
                    $sku,
                    $exception->getMessage()
                )
            );
            $isSalable = false;
        }

        return $isSalable;
    }
}
