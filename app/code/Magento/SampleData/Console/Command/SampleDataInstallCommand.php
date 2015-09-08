<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Console\Cli;
use Magento\Setup\Model\AdminAccount;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\State;
use Magento\Setup\Model\SampleData;
use Magento\Framework\Setup\ConsoleLogger;

/**
 * Command for installing Sample Data
 */
class SampleDataInstallCommand extends Command
{
    /**
     * Name of input option
     */
    const INPUT_KEY_MODULES = 'modules';

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var SampleData
     */
    private $sampleData;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param SampleData $sampleData
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory, SampleData $sampleData)
    {
        $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
        $params[State::PARAM_MODE] = State::MODE_DEVELOPER;
        $this->objectManager = $objectManagerFactory->create($params);
        $this->sampleData = $sampleData;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sampledata:install')
            ->setDescription('Installs sample data')
            ->setDefinition(
                [
                    new InputArgument(
                        AdminAccount::KEY_USER,
                        InputArgument::REQUIRED,
                        'Store\'s admin username'
                    ),
                    new InputOption(
                        self::INPUT_KEY_MODULES,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'Install sample data for comma-separated module(s)'
                    ),
                    new InputOption(
                        Cli::INPUT_KEY_BOOTSTRAP,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'Add or override parameters of the bootstrap'
                    ),
                ]
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminUserName = $input->getArgument(AdminAccount::KEY_USER);
        $modules = [];
        if ($input->getOption(self::INPUT_KEY_MODULES)) {
            $modules = $this->getRequestedModules($input->getOption(self::INPUT_KEY_MODULES));
        }
        $logger = new ConsoleLogger($output);
        $this->sampleData->install($this->objectManager, $logger, $adminUserName, $modules);
        $output->writeln('<info>' . 'Successfully installed sample data.' . '</info>');
    }

    /**
     * Retrieve requested modules
     *
     * @param string $modulesString
     * @return array
     */
    private function getRequestedModules($modulesString)
    {
        $modules = [];
        if ($modulesString) {
            foreach (explode(' ', str_replace(',', ' ', $modulesString)) as $module) {
                $modules[] = trim($module);
            }
        }
        return $modules;
    }
}
