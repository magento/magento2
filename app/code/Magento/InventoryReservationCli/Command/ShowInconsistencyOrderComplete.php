<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Command;

use Magento\InventoryReservationCli\Model\GetOrderInFinalState;
use Magento\InventoryReservationCli\Model\GetOrderWithBrokenReservation;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowInconsistencyOrderComplete extends Command
{
    /**
     * @var GetOrderWithBrokenReservation
     */
    private $getOrderWithBrokenReservation;
    /**
     * @var GetOrderInFinalState
     */
    private $getOrderInFinalState;

    /**
     * @param GetOrderWithBrokenReservation $getOrderWithBrokenReservation
     * @param GetOrderInFinalState $getOrderInFinalState
     */
    public function __construct(
        GetOrderWithBrokenReservation $getOrderWithBrokenReservation,
        GetOrderInFinalState $getOrderInFinalState
    ) {
        $this->getOrderWithBrokenReservation = $getOrderWithBrokenReservation;
        $this->getOrderInFinalState = $getOrderInFinalState;
        parent::__construct();
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
        /** @var array $orderBrokenReservation */
        $orderBrokenReservation = $this->getOrderWithBrokenReservation->execute();

        /** @var OrderInterface[] $orders */
        $orders = $this->getOrderInFinalState->execute(array_keys($orderBrokenReservation));

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
