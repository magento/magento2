<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\ResourceModel\Reservation\SaveMultiple;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationAppendInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class ReservationAppend implements ReservationAppendInterface
{
    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SaveMultiple $saveMultiple
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveMultiple $saveMultiple,
        LoggerInterface $logger
    ) {
        $this->saveMultiple = $saveMultiple;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $reservations)
    {
        /** @var ReservationInterface $reservation */
        foreach ($reservations as $reservation) {
            if (null !== $reservation->getReservationId()) {
                throw new InputException(__('Cannot update Reservation %1', $reservation->getReservationId()));
            }
        }
        try {
            $this->saveMultiple->execute($reservations);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not append Reservation'), $e);
        }
    }
}
