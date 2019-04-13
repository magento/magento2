<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Command;

use Magento\InventoryReservationCli\Model\GetSaleableQuantityInconsistencies;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Outputs a list of uncompensated reservations linked to the orders in final state (Completed, Closed, Canceled).
 *
 * This command may be used to simplify migrations from Magento versions without new Inventory or to track down
 * incorrect behavior of customizations.
 */
class ShowSaleableQuantityInconsistencies extends Command
{
    /**
     * @var GetSaleableQuantityInconsistencies
     */
    private $getSaleableQuantityInconsistencies;

    /**
     * @param GetSaleableQuantityInconsistencies $getSaleableQuantityInconsistencies
     */
    public function __construct(
        GetSaleableQuantityInconsistencies $getSaleableQuantityInconsistencies
    ) {
        parent::__construct();
        $this->getSaleableQuantityInconsistencies = $getSaleableQuantityInconsistencies;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('inventory:reservation:list-inconsistencies')
            ->setDescription('Show all orders and products with saleable quantity inconsistencies')
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_REQUIRED,
                'Filter for complete or incomplete orders'
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
     * @param SaleableQuantityInconsistency[] $inconsistencies
     */
    private function prettyOutput(OutputInterface $output, array $inconsistencies): void
    {
        $output->writeln('<comment>Inconsistencies found on following entries:</comment>');

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
                        '  - Product <comment>%s</comment> should be compensated by <comment>%+f</comment>',
                        $sku,
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
     * @param SaleableQuantityInconsistency[] $inconsistencies
     */
    private function rawOutput(OutputInterface $output, array $inconsistencies): void
    {
        /** @var Order $order */
        foreach ($inconsistencies as $inconsistency) {
            $inconsistentItems = $inconsistency->getItems();

            foreach ($inconsistentItems as $sku => $qty) {
                $output->writeln(
                    sprintf('%s:%s:%f', $inconsistency->getOrder()->getIncrementId(), $sku, -$qty)
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
        $inconsistencies = $this->getSaleableQuantityInconsistencies->execute();

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
