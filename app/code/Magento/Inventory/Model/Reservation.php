<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Inventory\Model\ResourceModel\Reservation as ReservationResourceModel;
use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class Reservation extends AbstractModel implements ReservationInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ReservationResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getReservationId()
    {
        return $this->getData(self::RESERVATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function getStockId(): int
    {
        return $this->getData(self::STOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritdoc
     */
    public function getQuantity(): float
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function getMetadata()
    {
        return $this->getData(self::METADATA);
    }
}
