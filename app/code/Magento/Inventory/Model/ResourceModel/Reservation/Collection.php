<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\Reservation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\ResourceModel\Reservation as ReservationResourceModel;
use Magento\Inventory\Model\Reservation as ReservationModel;

/**
 * Resource Collection of Reservation entities
 *
 * @api
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ReservationModel::class, ReservationResourceModel::class);
    }
}
