<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig\Reader as ConfigReader;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to check if the modules config in app/etc/config.php matches with how Magento interprets it
 */
class ModuleConfigStatusCommand extends Command
{
    /**
     * Deployment config reader
     *
     * @var ConfigReader
     */
    private $configReader;

    /**
     * Installer service factory.
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @param ConfigReader     $configReader
     * @param InstallerFactory $installerFactory
     */
    public function __construct(
        ConfigReader     $configReader,
        InstallerFactory $installerFactory
    ) {
        $this->configReader     = $configReader;
        $this->installerFactory = $installerFactory;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('module:config:status')
            ->setDescription(
                'Checks the modules configuration in the \'app/etc/config.php\' file '
                . 'and reports if they are up to date or not'
            );

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // the config as currently in app/etc/config.php
            $currentConfig = $this->configReader->load(ConfigFilePool::APP_CONFIG);
            if (!array_key_exists(ConfigOptionsListConstants::KEY_MODULES, $currentConfig)) {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception('Can\'t find the modules configuration in the \'app/etc/config.php\' file.');
            }

            $currentModuleConfig = $currentConfig[ConfigOptionsListConstants::KEY_MODULES];

            $installer = $this->installerFactory->create(new ConsoleLogger($output));

            // the module config as Magento calculated it
            $correctModuleConfig = $installer->getModulesConfig();

            if ($currentModuleConfig !== $correctModuleConfig) {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception(
                    'The modules configuration in the \'app/etc/config.php\' file is outdated. '
                    . 'Run \'setup:upgrade\' to fix it.'
                );
            }

            $output->writeln(
                '<info>The modules configuration is up to date.</info>'
            );
            // phpcs:disable Magento2.Exceptions.ThrowCatch
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
