<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\ConsoleLogger;
use Symfony\Component\Console\Input\InputOption;
use Magento\Setup\Model\ConfigModel;

/**
 * Command to install Magento application
 */
class InstallCommand extends AbstractSetupCommand
{
    /**
     * Parameter indicating command whether to cleanup database in the install routine
     */
    const INPUT_KEY_CLEANUP_DB = 'cleanup_database';

    /**
     * Parameter to specify an order_increment_prefix
     */
    const INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX = 'sales_order_increment_prefix';

    /**
     * Installer service factory
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @var ConfigModel
     */
    protected $configModel;

    /**
     * @var InstallUserConfigurationCommand
     */
    protected $userConfig;

    /**
     * @var AdminUserCreateCommand
     */
    protected $adminUser;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param ConfigModel $configModel
     * @param InstallUserConfigurationCommand $userConfig
     * @param AdminUserCreateCommand $adminUser
     */
    public function __construct(
        InstallerFactory $installerFactory,
        ConfigModel $configModel,
        InstallUserConfigurationCommand $userConfig,
        AdminUserCreateCommand $adminUser
    ) {
        $this->installerFactory = $installerFactory;
        $this->configModel = $configModel;
        $this->userConfig = $userConfig;
        $this->adminUser = $adminUser;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $inputOptionsArgs = $this->configModel->getAvailableOptions();
        $inputOptionsArgs = array_merge($inputOptionsArgs, $this->userConfig->getOptionsList());
        $inputOptionsArgs = array_merge($inputOptionsArgs, $this->adminUser->getOptionsList());
        $inputOptionsArgs = array_merge($inputOptionsArgs, [
            new InputOption(
                self::INPUT_KEY_CLEANUP_DB,
                null,
                InputOption::VALUE_NONE,
                'Cleanup the database before installation'
            ),
            new InputOption(
                self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX,
                null,
                InputOption::VALUE_REQUIRED,
                'Provide order number prefix'
            )
        ]);
        $this->setName('setup:install')
            ->setDescription('Installs Magento Application')
            ->setDefinition($inputOptionsArgs);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $installer = $this->installerFactory->create($consoleLogger);
        $inputOptionsArgs = $input->getArguments();
        $inputOptionsArgs = array_merge($inputOptionsArgs, $input->getOptions());
        $installer->install($inputOptionsArgs);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $inputOptions = $input->getOptions();
        $OptionsToValidate = [];
        foreach ($this->configModel->getAvailableOptions() as $option) {
            if (array_key_exists($option->getName(), $inputOptions)) {
                $OptionsToValidate[$option->getName()] = $inputOptions[$option->getName()];
            }
        }
        $errors = $this->configModel->validate($OptionsToValidate);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error>$error</error>");
            }
            throw new \InvalidArgumentException('Parameters validation is failed');
        }
    }
}
