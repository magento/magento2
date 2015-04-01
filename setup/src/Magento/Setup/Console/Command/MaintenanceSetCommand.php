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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceSetCommand extends Command
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
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = $this->getOptions();
        $this->setName('setup:maintenance:set')
            ->setDescription('Sets Magento maintenance mode')
            ->setDefinition($options);

        $this->ignoreValidationErrors();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $set = $input->getOption('set');
        $addresses = $input->getOption('addresses');

        if (null !== $set) {
            if (1 == $set) {
                $output->writeln('<info>Enabling maintenance mode...</info>');
                $this->maintenanceMode->set(true);
            } else {
                $output->writeln('<info>Disabling maintenance mode...</info>');
                $this->maintenanceMode->set(false);
            }
        }
        if (!empty($addresses)) {
            $addresses = implode(',', $addresses);
            $addresses = ('none' == $addresses) ? '' : $addresses;
            $this->maintenanceMode->setAddresses($addresses);
        }

        $output->writeln(
            '<info>Status: maintenance mode is ' .
            ($this->maintenanceMode->isOn() ? 'active' : 'not active') . '</info>'
        );
        $addressInfo = $this->maintenanceMode->getAddressInfo();
        if (!empty($addressInfo)) {
            $addresses = implode(', ', $addressInfo);
            $output->writeln('<info>List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none') . '</info>');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $inputOptions = $input->getOptions();
        $errors = $this->validate($inputOptions);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode("\n", $errors));
        }
    }

    /**
     * Validate input options
     *
     * @param array $inputOptions
     * @return array
     */
    private function validate($inputOptions)
    {
        $errors = [];
        if (isset($inputOptions['set'])) {
            if ($inputOptions['set'] !== '1' && $inputOptions['set'] !== '0') {
                $errors[] = 'Invalid value for \'set\', it must be 1 or 0';
            }
        }
        return $errors;
    }

    /**
     * Gets input options for the command
     *
     * @return InputOption[]
     */
    public function getOptions()
    {
        return [
            new InputOption('set', null, InputOption::VALUE_REQUIRED, 'Maintenance mode value (1/0)'),
            new InputOption(
                'addresses',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Maintenance mode addresses'
            ),
        ];
    }
}
