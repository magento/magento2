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
class IsSalableWithReservationsCondition implements IsProductSalableForRequestedQtyInterface
{
    /** @var \Magento\InventorySales\Model\IsProductSalableCondition\IsSalableWithReservationsCondition */
    private $isSalableWithReservationsCondition;

    public function __construct(
        \Magento\InventorySales\Model\IsProductSalableCondition\IsSalableWithReservationsCondition
        $isSalableWithReservationsCondition
    ) {
        $this->isSalableWithReservationsCondition = $isSalableWithReservationsCondition;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): bool
    {
        return $this->isSalableWithReservationsCondition->execute($sku, $stockId);
    }
}
