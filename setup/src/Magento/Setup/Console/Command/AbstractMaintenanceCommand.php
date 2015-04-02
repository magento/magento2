<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractMaintenanceCommand extends Command
{
    /**
     * Names of input option
     */
    const INPUT_KEY_IP = 'ip';

    /**
     * @var MaintenanceMode $maintenanceMode
     */
    protected $maintenanceMode;

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
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_IP,
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Allowed IP addresses'
            ),
        ];
        $this->setDefinition($options);
    }

    abstract protected function isEnable();

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addresses = $input->getOption(self::INPUT_KEY_IP);
        $this->maintenanceMode->set($this->isEnable());
        $displayString = $this->isEnable() ? 'Enabled' : 'Disabled';
        $output->writeln("<info>$displayString maintenance mode</info>");

        if (!empty($addresses)) {
            $addresses = implode(',', $addresses);
            $addresses = ('none' == $addresses) ? '' : $addresses;
            $this->maintenanceMode->setAddresses($addresses);
            $output->writeln(
                '<info>Set exempt IP-addresses: ' . (implode(', ', $this->maintenanceMode->getAddressInfo()) ?: 'none')
                . '</info>'
            );
        }
    }
}
