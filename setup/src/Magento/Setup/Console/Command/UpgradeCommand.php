<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for updating installed application after the code base has changed
 * @since 2.0.0
 */
class UpgradeCommand extends AbstractSetupCommand
{
    /**
     * Option to skip deletion of generated/code directory
     */
    const INPUT_KEY_KEEP_GENERATED = 'keep-generated';

    /**
     * Installer service factory
     *
     * @var InstallerFactory
     * @since 2.0.0
     */
    private $installerFactory;

    /**
     * @var DeploymentConfig
     * @since 2.2.0
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param DeploymentConfig $deploymentConfig
     * @since 2.0.0
     */
    public function __construct(InstallerFactory $installerFactory, DeploymentConfig $deploymentConfig = null)
    {
        $this->installerFactory = $installerFactory;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        parent::__construct();
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_KEEP_GENERATED,
                null,
                InputOption::VALUE_NONE,
                'Prevents generated files from being deleted. ' . PHP_EOL .
                'We discourage using this option except when deploying to production. ' . PHP_EOL .
                'Consult your system integrator or administrator for more information.'
            )
        ];
        $this->setName('setup:upgrade')
            ->setDescription('Upgrades the Magento application, DB data, and schema')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $keepGenerated = $input->getOption(self::INPUT_KEY_KEEP_GENERATED);
            $installer = $this->installerFactory->create(new ConsoleLogger($output));
            $installer->updateModulesSequence($keepGenerated);
            $installer->installSchema();
            $installer->installDataFixtures();

            if ($this->deploymentConfig->isAvailable()) {
                $importConfigCommand = $this->getApplication()->find(ConfigImportCommand::COMMAND_NAME);
                $arrayInput = new ArrayInput([]);
                $arrayInput->setInteractive($input->isInteractive());
                $importConfigCommand->run($arrayInput, $output);
            }

            if (!$keepGenerated) {
                $output->writeln(
                    '<info>Please re-run Magento compile command. Use the command "setup:di:compile"</info>'
                );
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
