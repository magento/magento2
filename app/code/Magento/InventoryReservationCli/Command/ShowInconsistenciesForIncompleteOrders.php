<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Command;

use Magento\InventoryReservationCli\Model\GetOrdersInNotFinalState;
use Magento\InventoryReservationCli\Model\GetOrdersWithMissingInitialReservations;
use Magento\InventoryReservationCli\Model\GetOrdersWithNotCompensatedReservations;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Outputs a list of incomplete orders, which have not a initial reservation.
 *
 * This command may be used to simplify migrations from Magento versions without new Inventory or to track down
 * incorrect behavior of customizations.
 */
class ShowInconsistenciesForIncompleteOrders extends Command
{
    /**
     * @var GetOrdersInNotFinalState
     */
    private $getOrdersInNotFinalState;

    /**
     * @var GetOrdersWithMissingInitialReservations
     */
    private $getOrdersWithMissingInitialReservations;

    /**
     * @param GetOrdersWithMissingInitialReservations $getOrdersWithMissingInitialReservations
     * @param GetOrdersInNotFinalState $getOrdersInNotFinalState
     */
    public function __construct(
        GetOrdersWithMissingInitialReservations $getOrdersWithMissingInitialReservations,
        GetOrdersInNotFinalState $getOrdersInNotFinalState
    ) {
        $this->getOrdersWithMissingInitialReservations = $getOrdersWithMissingInitialReservations;
        $this->getOrdersInNotFinalState = $getOrdersInNotFinalState;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('inventory:reservation:list-missing-reservation')
            ->setDescription('Show all orders and products without initial reservation')
            ->addOption('raw', 'r', InputOption::VALUE_NONE, 'Raw output');

        parent::configure();
    }

    /**
     * Format output
     *
     * @param OutputInterface $output
     * @param array $inconsistentData
     */
    private function prettyOutput(OutputInterface $output, array $inconsistentData): void
    {
        $output->writeln('<comment>Inconsistencies found on following entries:</comment>');

        /** @var Order $order */
        foreach ($inconsistentData as $inconsistentOrder) {
            $inconsistentSkus = $inconsistentOrder['skus'];
            $incrementId = $inconsistentOrder['increment_id'];

            $output->writeln(sprintf('Order <comment>%s</comment>:', $incrementId));

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
     * Output without formatting
     *
     * @param OutputInterface $output
     * @param array $inconsistentData
     */
    private function rawOutput(OutputInterface $output, array $inconsistentData): void
    {
        /** @var Order $order */
        foreach ($inconsistentData as $inconsistentOrder) {
            $inconsistentSkus = $inconsistentOrder['skus'];
            $incrementId = $inconsistentOrder['increment_id'];

            foreach ($inconsistentSkus as $inconsistentSku => $qty) {
                $output->writeln(
                    sprintf('%s:%s:%f', $incrementId, $inconsistentSku, -$qty)
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $incompleteOrders = $this->getOrdersInNotFinalState->execute();
        $inconsistentData = $this->getOrdersWithMissingInitialReservations->execute($incompleteOrders);

        if (empty($inconsistentData)) {
            $output->writeln('<info>No order inconsistencies were found</info>');
            return 0;
        }

        if ($input->getOption('raw')) {
            $this->rawOutput($output, $inconsistentData);
        } else {
            $this->prettyOutput($output, $inconsistentData);
        }
        return -1;
    }
}
