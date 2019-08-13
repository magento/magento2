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
use Magento\InventoryReservationCli\Command\Input\GetCommandlineStandardInput;
use Magento\InventoryReservationCli\Command\Input\GetReservationFromCompensationArgument;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
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
     * @var GetCommandlineStandardInput
     */
    private $getCommandlineStandardInput;

    /**
     * @var GetReservationFromCompensationArgument
     */
    private $getReservationFromCompensationArgument;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @param GetCommandlineStandardInput $getCommandlineStandardInput
     * @param GetReservationFromCompensationArgument $getReservationFromCompensationArgument
     * @param AppendReservationsInterface $appendReservations
     */
    public function __construct(
        GetCommandlineStandardInput $getCommandlineStandardInput,
        GetReservationFromCompensationArgument $getReservationFromCompensationArgument,
        AppendReservationsInterface $appendReservations
    ) {
        parent::__construct();
        $this->getCommandlineStandardInput = $getCommandlineStandardInput;
        $this->getReservationFromCompensationArgument = $getReservationFromCompensationArgument;
        $this->appendReservations = $appendReservations;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('inventory:reservation:create-compensations')
            ->setDescription('Create reservations by provided compensation arguments')
            ->addArgument(
                'compensations',
                InputArgument::IS_ARRAY,
                'List of compensation arguments in format "<ORDER_INCREMENT_ID>:<SKU>:<QUANTITY>:<STOCK-ID>"'
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
     * @param InputInterface $input
     * @return array
     * @throws InvalidArgumentException
     */
    private function getCompensationsArguments(InputInterface $input): array
    {
        $compensationArguments = $input->getArgument('compensations');

        if (empty($compensationArguments)) {
            $compensationArguments = $this->getCommandlineStandardInput->execute();
        }

        if (empty($compensationArguments)) {
            throw new InvalidArgumentException('A list of compensations needs to be defined as argument or STDIN.');
        }

        return $compensationArguments;
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ValidationException
     * @throws InputException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Following reservations were created:</info>');

        $hasErrors = false;
        foreach ($this->getCompensationsArguments($input) as $compensationsArgument) {
            try {
                $compensation = $this->getReservationFromCompensationArgument->execute($compensationsArgument);
                $this->appendReservations->execute([$compensation]);
                $output->writeln(
                    sprintf(
                        '  - Product <comment>%s</comment> was compensated by '
                        . '<comment>%+f</comment> for stock <comment>%s</comment>',
                        $compensation->getSku(),
                        $compensation->getQuantity(),
                        $compensation->getStockId()
                    )
                );
            } catch (CouldNotSaveException $exception) {
                $hasErrors = true;
                $output->writeln(sprintf(' - <error>%s</error>', $exception->getMessage()));
            } catch (InvalidArgumentException $exception) {
                $hasErrors = true;
                $output->writeln(sprintf(
                    '  - <error>Error while parsing argument "%s". %s</error>',
                    $compensationsArgument,
                    $exception->getMessage()
                ));
            } catch (\Exception $exception) {
                $output->writeln(sprintf(
                    '  - <error>Argument "%s" caused exception "%s"</error>',
                    $compensationsArgument,
                    $exception->getMessage()
                ));
            }
        }

        return $hasErrors ? 1 : 0;
    }
}
