<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Command;

use Magento\InventoryReservationCli\Model\GetSalableQuantityInconsistencies;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\FilterCompleteOrders;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\FilterIncompleteOrders;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Outputs a list of uncompensated reservations linked to the orders
 *
 * This command may be used to simplify migrations from Magento versions without new Inventory or to track down
 * incorrect behavior of customizations.
 */
class ShowInconsistencies extends Command
{
    /**
     * @var GetSalableQuantityInconsistencies
     */
    private $getSalableQuantityInconsistencies;

    /**
     * @var FilterCompleteOrders
     */
    private $filterCompleteOrders;

    /**
     * @var FilterIncompleteOrders
     */
    private $filterIncompleteOrders;

    /**
     * @param GetSalableQuantityInconsistencies $getSalableQuantityInconsistencies
     * @param FilterCompleteOrders $filterCompleteOrders
     * @param FilterIncompleteOrders $filterIncompleteOrders
     */
    public function __construct(
        GetSalableQuantityInconsistencies $getSalableQuantityInconsistencies,
        FilterCompleteOrders $filterCompleteOrders,
        FilterIncompleteOrders $filterIncompleteOrders
    ) {
        parent::__construct();
        $this->getSalableQuantityInconsistencies = $getSalableQuantityInconsistencies;
        $this->filterCompleteOrders = $filterCompleteOrders;
        $this->filterIncompleteOrders = $filterIncompleteOrders;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('inventory:reservation:list-inconsistencies')
            ->setDescription('Show all orders and products with salable quantity inconsistencies')
            ->addOption(
                'complete-orders',
                'c',
                InputOption::VALUE_NONE,
                'Show only inconsistencies for complete orders'
            )
            ->addOption(
                'incomplete-orders',
                'i',
                InputOption::VALUE_NONE,
                'Show only inconsistencies for incomplete orders'
            )
            ->addOption(
                'raw',
                'r',
                InputOption::VALUE_NONE,
                'Raw output'
            );

        parent::configure();
    }

    /**
     * Format output
     *
     * @param OutputInterface $output
     * @param SalableQuantityInconsistency[] $inconsistencies
     */
    private function prettyOutput(OutputInterface $output, array $inconsistencies): void
    {
        $output->writeln('<info>Inconsistencies found on following entries:</info>');

        /** @var Order $order */
        foreach ($inconsistencies as $inconsistency) {
            $inconsistentItems = $inconsistency->getItems();

            $output->writeln(sprintf(
                'Order <comment>%s</comment>:',
                $inconsistency->getOrder()->getIncrementId()
            ));

            foreach ($inconsistentItems as $sku => $qty) {
                $output->writeln(
                    sprintf(
                        '  - Product <comment>%s</comment> should be compensated by '
                        . '<comment>%+f</comment> for stock <comment>%s</comment>',
                        $sku,
                        -$qty,
                        $inconsistency->getStockId()
                    )
                );
            }
        }
    }

    /**
     * Output without formatting
     *
     * @param OutputInterface $output
     * @param SalableQuantityInconsistency[] $inconsistencies
     */
    private function rawOutput(OutputInterface $output, array $inconsistencies): void
    {
        /** @var Order $order */
        foreach ($inconsistencies as $inconsistency) {
            $inconsistentItems = $inconsistency->getItems();

            foreach ($inconsistentItems as $sku => $qty) {
                $output->writeln(
                    sprintf(
                        '%s:%s:%f:%s',
                        $inconsistency->getOrder()->getIncrementId(),
                        $sku,
                        -$qty,
                        $inconsistency->getStockId()
                    )
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
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();

        if ($input->getOption('complete-orders')) {
            $inconsistencies = $this->filterCompleteOrders->execute($inconsistencies);
        } elseif ($input->getOption('incomplete-orders')) {
            $inconsistencies = $this->filterIncompleteOrders->execute($inconsistencies);
        }

        if (empty($inconsistencies)) {
            $output->writeln('<info>No order inconsistencies were found</info>');
            return 0;
        }

        if ($input->getOption('raw')) {
            $this->rawOutput($output, $inconsistencies);
        } else {
            $this->prettyOutput($output, $inconsistencies);
        }

        return -1;
    }
}
