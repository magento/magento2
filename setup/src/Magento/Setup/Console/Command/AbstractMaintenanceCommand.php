<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Setup\Validator\IpValidator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractMaintenanceCommand extends AbstractSetupCommand
{
    /**
     * Names of input option
     */
    const INPUT_KEY_IP = 'ip';

    /**
     * @var MaintenanceMode
     */
    protected $maintenanceMode;

    /**
     * @var IpValidator
     */
    protected $ipValidator;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     * @param IpValidator $ipValidator
     */
    public function __construct(MaintenanceMode $maintenanceMode, IpValidator $ipValidator)
    {
        $this->maintenanceMode = $maintenanceMode;
        $this->ipValidator = $ipValidator;
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
                "Allowed IP addresses (use 'none' to clear allowed IP list)"
            ),
        ];
        $this->setDefinition($options);
        parent::configure();
    }

    /**
     * Get maintenance mode to set
     *
     * @return bool
     */
    abstract protected function isEnable();

    /**
     * Get display string after mode is set
     *
     * @return string
     */
    abstract protected function getDisplayString();

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addresses = $input->getOption(self::INPUT_KEY_IP);
        $messages = $this->validate($addresses);
        if (!empty($messages)) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $messages));
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $this->maintenanceMode->set($this->isEnable());
        $output->writeln($this->getDisplayString());

        if (!empty($addresses)) {
            $addresses = implode(',', $addresses);
            $addresses = ('none' == $addresses) ? '' : $addresses;
            $this->maintenanceMode->setAddresses($addresses);
            $output->writeln(
                '<info>Set exempt IP-addresses: ' . (implode(', ', $this->maintenanceMode->getAddressInfo()) ?: 'none')
                . '</info>'
            );
        }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Validates IP addresses and return error messages
     *
     * @param string[] $addresses
     * @return string[]
     */
    protected function validate(array $addresses)
    {
        return $this->ipValidator->validateIps($addresses, true);
    }
}
