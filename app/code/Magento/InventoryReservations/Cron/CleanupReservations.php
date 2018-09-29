<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Cron;

use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;

/**
 * Cron job precessing of reservations cleanup
 */
class CleanupReservations
{
    /**
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    /**
     * @param CleanupReservationsInterface $cleanupReservations
     */
    public function __construct(CleanupReservationsInterface $cleanupReservations)
    {
        $this->cleanupReservations = $cleanupReservations;
    }

    /**
     * Cleanup reservations
     *
     * @return void
     */
    public function execute()
    {
        $this->cleanupReservations->execute();
    }
}
