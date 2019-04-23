<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryReservationCli\Model\GetSalableQuantityCompensations\Proxy as GetSalableQuantityCompensations;
use Magento\InventoryReservationCli\Model\GetSalableQuantityInconsistencies\Proxy as GetSalableQuantityInconsistencies;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\FilterCompleteOrders\Proxy as FilterCompleteOrders;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\FilterIncompleteOrders\Proxy as FilterIncompleteOrders;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface\Proxy as AppendReservationsInterface;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create compensations for detected inconsistencies
 *
 * This command may be used to simplify migrations from Magento versions without new Inventory or to track down
 * incorrect behavior of customizations.
 */
class CreateCompensations extends Command
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
     * @var GetSalableQuantityCompensations
     */
    private $getSalableQuantityCompensations;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @param GetSalableQuantityInconsistencies $getSalableQuantityInconsistencies
     * @param GetSalableQuantityCompensations $getSalableQuantityCompensations
     * @param AppendReservationsInterface $appendReservations
     * @param FilterCompleteOrders $filterCompleteOrders
     * @param FilterIncompleteOrders $filterIncompleteOrders
     */
    public function __construct(
        GetSalableQuantityInconsistencies $getSalableQuantityInconsistencies,
        GetSalableQuantityCompensations $getSalableQuantityCompensations,
        AppendReservationsInterface $appendReservations,
        FilterCompleteOrders $filterCompleteOrders,
        FilterIncompleteOrders $filterIncompleteOrders
    ) {
        parent::__construct();
        $this->getSalableQuantityInconsistencies = $getSalableQuantityInconsistencies;
        $this->filterCompleteOrders = $filterCompleteOrders;
        $this->filterIncompleteOrders = $filterIncompleteOrders;
        $this->getSalableQuantityCompensations = $getSalableQuantityCompensations;
        $this->appendReservations = $appendReservations;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('inventory:reservation:create-compensations')
            ->setDescription('Create compensation reservations for detected inconsistencies')
            ->addArgument(
                'compensations',
                InputArgument::IS_ARRAY,
                'List of compensation arguments in format '
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
     * @param ReservationInterface[] $compensations
     */
    private function prettyOutput(OutputInterface $output, array $compensations): void
    {
        $output->writeln('<info>Following reservations were created:</info>');

        foreach ($compensations as $reservation) {
            $output->writeln(
                sprintf(
                    'Product <comment>%s</comment> compensated by <comment>%+f</comment> for stock id <comment>%s</comment>',
                    $reservation->getSku(),
                    $reservation->getQuantity(),
                    $reservation->getStockId()
                )
            );
        }
    }

    /**
     * Output without formatting
     *
     * @param OutputInterface $output
     * @param ReservationInterface[] $compensations
     */
    private function rawOutput(OutputInterface $output, array $compensations): void
    {
        foreach ($compensations as $reservation) {
            $output->writeln(
                sprintf(
                    '%s:%f:%s',
                    $reservation->getSku(),
                    $reservation->getQuantity(),
                    $reservation->getStockId()
                )
            );
        }
    }

    /**
     * @param InputInterface $input
     * @return SalableQuantityInconsistency[]
     * @throws ValidationException
     */
    private function getFilteredInconsistencies(InputInterface $input): array
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();

        if ($input->getOption('complete-orders')) {
            $inconsistencies = $this->filterCompleteOrders->execute($inconsistencies);
        } elseif ($input->getOption('incomplete-orders')) {
            $inconsistencies = $this->filterIncompleteOrders->execute($inconsistencies);
        }

        return $inconsistencies;
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ValidationException
     * @throws InputException
     * @throws CouldNotSaveException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $inconsistencies = $this->getFilteredInconsistencies($input);
        $compensations = $this->getSalableQuantityCompensations->execute($inconsistencies);

        if (empty($compensations)) {
            $output->writeln('<info>No required compensations calculated.</info>');
            return 0;
        }

        if (!$input->getOption('dry-run')) {
            $this->appendReservations->execute($compensations);
        }

        if ($input->getOption('raw')) {
            $this->rawOutput($output, $compensations);
        } else {
            $this->prettyOutput($output, $compensations);
        }

        return 0;
    }
}
