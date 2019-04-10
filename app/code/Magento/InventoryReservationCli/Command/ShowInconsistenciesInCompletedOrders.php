<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Command;

use Magento\InventoryReservationCli\Model\GetOrdersInFinalState;
use Magento\InventoryReservationCli\Model\GetOrdersWithNotCompensatedReservations;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowInconsistenciesInCompletedOrders extends Command
{
    /**
     * @var GetOrdersInFinalState
     */
    private $getOrderInFinalState;
    /**
     * @var GetOrdersWithNotCompensatedReservations
     */
    private $getOrdersWithNotCompensatedReservations;

    /**
     * @param GetOrdersWithNotCompensatedReservations $getOrdersWithNotCompensatedReservations
     * @param GetOrdersInFinalState $getOrderInFinalState
     */
    public function __construct(
        GetOrdersWithNotCompensatedReservations $getOrdersWithNotCompensatedReservations,
        GetOrdersInFinalState $getOrderInFinalState
    ) {
        $this->getOrdersWithNotCompensatedReservations = $getOrdersWithNotCompensatedReservations;
        $this->getOrderInFinalState = $getOrderInFinalState;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('inventory:reservation:list-not-compensated')
            ->setDescription('Show all orders and products without reservation compensation')
            ->addOption('raw', 'r', InputOption::VALUE_NONE, 'Raw output');

        parent::configure();
    }

    /**
     * @param OutputInterface $output
     * @param array $itemsNotCompensated
     */
    private function prettyOutput(OutputInterface $output, array $itemsNotCompensated): void
    {
        /** @var OrderInterface[] $orders */
        $orders = $this->getOrderInFinalState->execute(array_keys($itemsNotCompensated));

        $output->writeln('<comment>Inconsistencies found on following entries:</comment>');

        /** @var Order $order */
        foreach($orders as $order) {
            $inconsistentSkus = $itemsNotCompensated[$order->getId()];

            $output->writeln(sprintf('Order <comment>%s</comment>:', $order->getIncrementId()));

            foreach ($inconsistentSkus as $inconsistentSku => $qty) {
                $output->writeln(
                    sprintf(
                        '  - Product <comment>%s</comment> should be compensated by <comment>%+f</comment>',
                        $inconsistentSku,
                        -$qty
                    )
                );
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param array $itemsNotCompensated
     */
    private function rawOutput(OutputInterface $output, array $itemsNotCompensated): void
    {
        /** @var OrderInterface[] $orders */
        $orders = $this->getOrderInFinalState->execute(array_keys($itemsNotCompensated));

        /** @var Order $order */
        foreach($orders as $order) {
            $inconsistentSkus = $itemsNotCompensated[$order->getId()];

            foreach ($inconsistentSkus as $inconsistentSku => $qty) {
                $output->writeln(
                    sprintf('%s:%s:%f', $order->getIncrementId(), $inconsistentSku, -$qty)
                );
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array $itemsNotCompensated */
        $itemsNotCompensated = $this->getOrdersWithNotCompensatedReservations->execute();

        if (empty($itemsNotCompensated)) {
            $output->writeln('<info>No order inconsistencies were found</info>');
            return 0;
        }

        if ($input->getOption('raw')) {
            $this->rawOutput($output, $itemsNotCompensated);
        } else {
            $this->prettyOutput($output, $itemsNotCompensated);
        }
        return -1;
    }
}
