<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

/**
 * @inheritdoc
 */
class BackOrderCondition implements IsProductSalableForRequestedQtyInterface
{
    /** @var \Magento\InventorySales\Model\IsProductSalableCondition\BackOrderCondition */
    private $backOrderCondition;

    public function __construct(
        \Magento\InventorySales\Model\IsProductSalableCondition\BackOrderCondition $backOrderCondition
    ) {
        $this->backOrderCondition = $backOrderCondition;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): bool
    {
        return $this->backOrderCondition->execute($sku, $stockId);
    }
}
