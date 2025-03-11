<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking maintenance mode status
 */
class MaintenanceStatusCommand extends AbstractSetupCommand
{
    public const NAME = 'maintenance:status';

    /**
     * @var MaintenanceMode $maintenanceMode
     */
    private $maintenanceMode;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;

        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Displays maintenance mode status');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            '<info>Status: maintenance mode is ' .
            ($this->maintenanceMode->isOn() ? 'enabled' : 'disabled') . '</info>'
        );
        $addressInfo = $this->maintenanceMode->getAddressInfo();
        $addresses = implode(' ', $addressInfo);
        $output->writeln('<info>List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none') . '</info>');

        return Cli::RETURN_SUCCESS;
    }
}
