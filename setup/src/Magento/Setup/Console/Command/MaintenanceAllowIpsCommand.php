<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Validator\IpValidator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for setting allowed IPs in maintenance mode
 */
class MaintenanceAllowIpsCommand extends AbstractSetupCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_IP = 'ip';
    const INPUT_KEY_NONE = 'none';

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var IpValidator
     */
    private $ipValidator;

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
        $arguments = [
            new InputArgument(
                self::INPUT_KEY_IP,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Allowed IP addresses'
            ),
        ];
        $options = [
            new InputOption(
                self::INPUT_KEY_NONE,
                null,
                InputOption::VALUE_NONE,
                'Clear allowed IP addresses'
            ),
        ];
        $this->setName('maintenance:allow-ips')
            ->setDescription('Sets maintenance mode exempt IPs')
            ->setDefinition(array_merge($arguments, $options));
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption(self::INPUT_KEY_NONE)) {
            $addresses = $input->getArgument(self::INPUT_KEY_IP);
            $messages = $this->validate($addresses);
            if (!empty($messages)) {
                $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $messages));
                // we must have an exit code higher than zero to indicate something was wrong
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

            if (!empty($addresses)) {
                $this->maintenanceMode->setAddresses(implode(',', $addresses));
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

    /**
     * Validates IP addresses and return error messages
     *
     * @param string[] $addresses
     * @return string[]
     */
    protected function validate(array $addresses)
    {
        return $this->ipValidator->validateIps($addresses, false);
    }
}
