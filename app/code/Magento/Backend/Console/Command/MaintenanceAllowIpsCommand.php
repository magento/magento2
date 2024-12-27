<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Magento\Backend\Model\Validator\IpValidator;
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
    public const INPUT_KEY_IP = 'ip';
    public const INPUT_KEY_NONE = 'none';
    public const INPUT_KEY_ADD = 'add';
    public const NAME = 'maintenance:allow-ips';

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var IpValidator
     */
    private $ipValidator;

    /**
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
    protected function configure(): void
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
            new InputOption(
                self::INPUT_KEY_ADD,
                null,
                InputOption::VALUE_NONE,
                'Add the IP address to existing list'
            ),
        ];
        $this->setName(self::NAME)
            ->setDescription('Sets maintenance mode exempt IPs')
            ->setDefinition(array_merge($arguments, $options));

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
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
                if ($input->getOption(self::INPUT_KEY_ADD)) {
                    $addresses = array_unique(array_merge($this->maintenanceMode->getAddressInfo(), $addresses));
                }
                $this->maintenanceMode->setAddresses(implode(',', $addresses));
                $output->writeln(
                    '<info>Set exempt IP-addresses: ' . implode(' ', $this->maintenanceMode->getAddressInfo()) .
                    '</info>'
                );
            }
        } else {
            $this->maintenanceMode->setAddresses('');
            $output->writeln('<info>Set exempt IP-addresses: none</info>');
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Validates IP addresses and return error messages
     *
     * @param string[] $addresses
     * @return string[]
     */
    protected function validate(array $addresses): array
    {
        return $this->ipValidator->validateIps($addresses, false);
    }
}
