<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistencyFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Collects all existing and missing reservations in order to calculate inconsistency
 */
class Collector
{
    /**
     * @var SaleableQuantityInconsistency[]
     */
    private $inconsistencies = [];

    /**
     * @var \Magento\InventoryReservationCli\Model\SaleableQuantityInconsistencyFactory
     */
    private $saleableQuantityInconsistencyFactory;

    /**
     * @param SaleableQuantityInconsistencyFactory $saleableQuantityInconsistencyFactory
     */
    public function __construct(
        SaleableQuantityInconsistencyFactory $saleableQuantityInconsistencyFactory
    ) {
        $this->saleableQuantityInconsistencyFactory = $saleableQuantityInconsistencyFactory;
    }

    /**
     * @param int $objectId
     * @param string $sku
     * @param float $quantity
     * @param OrderInterface|null $order
     */
    public function add(int $objectId, string $sku, float $quantity, ?OrderInterface $order = null): void
    {
        if (!isset($this->inconsistencies[$objectId])) {
            $this->inconsistencies[$objectId] = $this->saleableQuantityInconsistencyFactory->create();
        }

        $this->inconsistencies[$objectId]->setObjectId($objectId);

        if ($order) {
            $this->inconsistencies[$objectId]->setOrder($order);
        }

        $this->inconsistencies[$objectId]->addItemQty($sku, $quantity);
    }

    /**
     * @return SaleableQuantityInconsistency[]
     */
    public function getInconsistencies(): array
    {
        return $this->inconsistencies;
    }

    /**
     * @param SaleableQuantityInconsistency[] $inconsistencies
     */
    public function setInconsistencies(array $inconsistencies)
    {
        $this->inconsistencies = $inconsistencies;
    }
}
