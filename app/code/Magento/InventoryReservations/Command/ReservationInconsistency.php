<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Command;

use Magento\InventoryReservations\Model\GetOrderWithBrokenReservation;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReservationInconsistency extends Command
{
    /**
     * @var GetOrderWithBrokenReservation
     */
    private $getOrderWithBrokenReservation;

    /**
     * @param GetOrderWithBrokenReservation $getOrderWithBrokenReservation
     */
    public function __construct(
        GetOrderWithBrokenReservation $getOrderWithBrokenReservation
    ) {
        parent::__construct();
        $this->getOrderWithBrokenReservation = $getOrderWithBrokenReservation;
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
        /** @var OrderInterface[] $orders */
        $orders = $this->getOrderWithBrokenReservation->execute();

        /** @var Order $order */
        foreach($orders as $order) {
            $output->writeln(
                __('Order %1 got inconsistency on inventory reservation',
                    $order->getIncrementId()
                )
            );
        }
    }
}
