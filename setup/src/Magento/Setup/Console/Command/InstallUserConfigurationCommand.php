<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\ConsoleLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallUserConfigurationCommand extends AbstractSetupCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_BASE_URL = 'base_url';
    const INPUT_LANGUAGE = 'language';
    const INPUT_TIMEZONE = 'timezone';
    const INPUT_CURRENCY = 'currency';
    const INPUT_USE_REWRITES = 'use_rewrites';
    const INPUT_USE_SECURE = 'use_secure';
    const INPUT_BASE_URL_SECURE = 'base_url_secure';

    /**
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Inject dependencies
     *
     * @param InstallerFactory $installerFactory
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        InstallerFactory $installerFactory,
        DeploymentConfig $deploymentConfig
    ) {
        $this->installerFactory = $installerFactory;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:user-config:set')
            ->setDescription('Installs admin user account')
            ->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln("<info>User settings can't be saved: the application is not installed.</info>");
            return;
        }
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->installUserConfig($input->getOptions());
    }

    /**
     * Get list of options for the command
     *
     * @return InputOption[]
     */
    public function getOptionsList()
    {
        return [
            new InputOption(
                self::INPUT_BASE_URL,
                null,
                InputOption::VALUE_REQUIRED,
                'Base URL'
            ),
            new InputOption(
                self::INPUT_LANGUAGE,
                null,
                InputOption::VALUE_REQUIRED,
                'Language locale'
            ),
            new InputOption(
                self::INPUT_TIMEZONE,
                null,
                InputOption::VALUE_REQUIRED,
                'Time zone'
            ),
            new InputOption(
                self::INPUT_CURRENCY,
                null,
                InputOption::VALUE_REQUIRED,
                'Currency'
            ),
            new InputOption(
                self::INPUT_USE_REWRITES,
                null,
                InputOption::VALUE_REQUIRED,
                'Use rewrites'
            ),
            new InputOption(
                self::INPUT_USE_SECURE,
                null,
                InputOption::VALUE_REQUIRED,
                'Use secure'
            ),
            new InputOption(
                self::INPUT_BASE_URL_SECURE,
                null,
                InputOption::VALUE_REQUIRED,
                'Base URL secure'
            ),
        ];
    }
}
