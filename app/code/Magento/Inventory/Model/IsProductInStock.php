<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Inventory\Model\Stock\Command\GetProductQuantityInterface;
use Magento\InventoryApi\Api\IsProductInStockInterface;

/**
 * Return product availability by Product SKU and Stock Id.
 *
 * @see \Magento\InventoryApi\Api\GetProductQuantityInStockInterface
 * @see \Magento\Inventory\Model\Stock\Command\IsInStockInterface
 * @api
 */
class IsProductInStock implements IsProductInStockInterface
{
    /**
     * @var GetProductQuantityInterface
     */
    private $commandGetProductQuantity;

    /**
     * IsProductInStock constructor.
     *
     * @param GetProductQuantityInterface $getProductQuantity
     */
    public function __construct(
        GetProductQuantityInterface $commandGetProductQuantity
    ) {
        $this->commandGetProductQuantity = $commandGetProductQuantity;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        return $this->commandGetProductQuantity->execute($sku, $stockId) > 0;
    }
}
