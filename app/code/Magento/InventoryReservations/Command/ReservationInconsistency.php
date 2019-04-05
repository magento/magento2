<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Command;

use Magento\InventoryReservations\Model\GetReservationsTotOrder\Proxy as GetReservationsTotOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportCustomersCommand
 */
class ReservationInconsistency extends Command
{
    /**
     * @var \Magento\InventoryReservations\Model\GetReservationsTotOrder $getReservationsTotOrder
     */
    private $getReservationsTotOrder;

    /**
     * ReservationInconsistency constructor.
     * @param GetReservationsTotOrder $getReservationsTotOrder
     */
    public function __construct(
        GetReservationsTotOrder $getReservationsTotOrder
    ) {
        parent::__construct();
        $this->getReservationsTotOrder = $getReservationsTotOrder;
    }

    protected function configure()
    {
        $this
            ->setName('inventory:reservation:show-inconsistency')
            ->setDescription('Show all reservation inconsistencies for completed orders');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var array $orderListReservations */
        $orderListReservations = $this->getReservationsTotOrder->getListReservationsTotOrder();

        foreach ($orderListReservations as $orderReservationTot){
            $output->writeln(
                __('Order %1 got inconsistency on reservation by %2',
                    $orderReservationTot['IncrementId'],
                    $orderReservationTot['ReservationTot']
                )
            );
        }
    }
}
