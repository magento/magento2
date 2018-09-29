<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\InventoryReservations\Model\ResourceModel\SaveMultiple;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class AppendReservations implements AppendReservationsInterface
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
    public function execute(array $reservations): void
    {
        if (empty($reservations)) {
            throw new InputException(__('Input data is empty'));
        }

        /** @var ReservationInterface $reservation */
        foreach ($reservations as $reservation) {
            if (null !== $reservation->getReservationId()) {
                $message =  __(
                    'Cannot update Reservation %reservation',
                    ['reservation' => $reservation->getReservationId()]
                );
                $this->logger->error($message);
                throw new InputException($message);
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
