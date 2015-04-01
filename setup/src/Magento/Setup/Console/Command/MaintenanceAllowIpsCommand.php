<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Module\ModuleList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceAllowIpsCommand extends Command
{
    /**
     * @var MaintenanceMode $maintenanceMode
     */
    private $maintenanceMode;

    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
        parent::__construct();
    }

    /**
     * Gets input arguments for the command
     *
     * @return InputArgument[]
     */
    public function getArguments()
    {
        return [
            new InputArgument(
                'ip',
                InputArgument::OPTIONAL,
                'Allowed IP addresses'
            ),
        ];
    }

    /**
     * Gets input arguments for the command
     *
     * @return InputArgument[]
     */
    public function getOptions()
    {
        return [
            new InputOption(
                'none',
                null,
                InputOption::VALUE_NONE,
                'Clear allowed IP addresses'
            ),
        ];
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $arguments = $this->getArguments();
        $options = $this->getOptions();
        $this->setName('maintenance:allow-ips')
            ->setDescription('Set maintenance mode exempt IPs')
            ->setDefinition(array_merge($arguments, $options));

        $this->ignoreValidationErrors();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('none')) {
            $addresses = $input->getArgument('ip');
            if (!empty($addresses)) {
                $this->maintenanceMode->setAddresses($addresses);
                $output->writeln(
                    '<info>Set exempt IP-addresses: ' . implode(', ', $this->maintenanceMode->getAddressInfo()) .
                    '</info>'
                );
            }
        } else {
            $this->maintenanceMode->setAddresses('');
            $output->writeln('<info>Set exempt IP-addresses: none</info>');
        }


    }
}
