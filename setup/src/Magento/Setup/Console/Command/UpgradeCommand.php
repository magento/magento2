<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\Framework\Setup\Declaration\Schema\OperationsExecutor;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for updating installed application after the code base has changed.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeCommand extends AbstractSetupCommand
{
    /**
     * Option to skip deletion of generated/code directory.
     */
    const INPUT_KEY_KEEP_GENERATED = 'keep-generated';

    /**
     * Installer service factory.
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @param InstallerFactory $installerFactory
     * @param DeploymentConfig $deploymentConfig
     * @param AppState|null $appState
     */
    public function __construct(
        InstallerFactory $installerFactory,
        DeploymentConfig $deploymentConfig = null,
        AppState $appState = null
    ) {
        $this->installerFactory = $installerFactory;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->appState = $appState ?: ObjectManager::getInstance()->get(AppState::class);
        parent::__construct();
    }

    /**
     * @inheritdoc
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
            ),
            new InputOption(
                InstallCommand::CONVERT_OLD_SCRIPTS_KEY,
                null,
                InputOption::VALUE_OPTIONAL,
                'Allows to convert old scripts (InstallSchema, UpgradeSchema) to db_schema.xml format',
                false
            ),
            new InputOption(
                OperationsExecutor::KEY_SAFE_MODE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Safe installation of Magento with dumps on destructive operations, like column removal'
            ),
            new InputOption(
                OperationsExecutor::KEY_DATA_RESTORE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Restore removed data from dumps'
            ),
            new InputOption(
                DryRunLogger::INPUT_KEY_DRY_RUN_MODE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Magento Installation will be run in dry-run mode',
                false
            )
        ];
        $this->setName('setup:upgrade')
            ->setDescription('Upgrades the Magento application, DB data, and schema')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $request = $input->getOptions();
            $keepGenerated = $input->getOption(self::INPUT_KEY_KEEP_GENERATED);
            $installer = $this->installerFactory->create(new ConsoleLogger($output));
            $installer->updateModulesSequence($keepGenerated);
            $installer->installSchema($request);
            $installer->installDataFixtures($request);

            if ($this->deploymentConfig->isAvailable()) {
                $importConfigCommand = $this->getApplication()->find(ConfigImportCommand::COMMAND_NAME);
                $arrayInput = new ArrayInput([]);
                $arrayInput->setInteractive($input->isInteractive());
                $importConfigCommand->run($arrayInput, $output);
            }

            if (!$keepGenerated && $this->appState->getMode() === AppState::MODE_PRODUCTION) {
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
