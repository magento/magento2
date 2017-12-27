<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Cron;

use Magento\Inventory\Model\CleanupReservationsInterface;

class CleanupReservations
{
    /**
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    public function __construct(CleanupReservationsInterface $cleanupReservations)
    {
        $this->cleanupReservations = $cleanupReservations;
    }

    /**
     * Cleanup reservations
     *
     * @return void
     */
    public function execute(): void
    {
        $this->cleanupReservations->execute();
    }
}
