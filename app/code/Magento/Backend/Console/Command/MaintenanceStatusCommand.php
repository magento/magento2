<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->setName('maintenance:status')
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
            ($this->maintenanceMode->isOn() ? 'active' : 'not active') . '</info>'
        );
        $addressInfo = $this->maintenanceMode->getAddressInfo();
        $addresses = implode(' ', $addressInfo);
        $output->writeln('<info>List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none') . '</info>');

        return Cli::RETURN_SUCCESS;
    }
}
