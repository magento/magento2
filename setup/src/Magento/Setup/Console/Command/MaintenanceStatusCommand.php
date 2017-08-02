<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Module\ModuleList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking maintenance mode status
 * @since 2.0.0
 */
class MaintenanceStatusCommand extends AbstractSetupCommand
{
    /**
     * @var MaintenanceMode $maintenanceMode
     * @since 2.0.0
     */
    private $maintenanceMode;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('maintenance:status')
            ->setDescription('Displays maintenance mode status');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            '<info>Status: maintenance mode is ' .
            ($this->maintenanceMode->isOn() ? 'active' : 'not active') . '</info>'
        );
        $addressInfo = $this->maintenanceMode->getAddressInfo();
        $addresses = implode(', ', $addressInfo);
        $output->writeln('<info>List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none') . '</info>');
    }
}
