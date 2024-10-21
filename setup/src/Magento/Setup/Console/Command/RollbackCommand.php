<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Backup\Factory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to rollback code, media and DB
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RollbackCommand extends AbstractSetupCommand
{
    /**
     * Name of input arguments or options
     */
    public const INPUT_KEY_CODE_BACKUP_FILE = 'code-file';
    public const INPUT_KEY_MEDIA_BACKUP_FILE = 'media-file';
    public const INPUT_KEY_DB_BACKUP_FILE = 'db-file';
    public const NAME = 'setup:rollback';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * Existing deployment config
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var MaintenanceModeEnabler
     */
    private $maintenanceModeEnabler;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode deprecated, use $maintenanceModeEnabler instead
     * @param DeploymentConfig $deploymentConfig
     * @param MaintenanceModeEnabler $maintenanceModeEnabler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        DeploymentConfig $deploymentConfig,
        MaintenanceModeEnabler $maintenanceModeEnabler = null
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->backupRollbackFactory = $this->objectManager->get(\Magento\Framework\Setup\BackupRollbackFactory::class);
        $this->deploymentConfig = $deploymentConfig;
        $this->maintenanceModeEnabler =
            $maintenanceModeEnabler ?: $this->objectManager->get(MaintenanceModeEnabler::class);
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_CODE_BACKUP_FILE,
                'c',
                InputOption::VALUE_REQUIRED,
                'Basename of the code backup file in var/backups'
            ),
            new InputOption(
                self::INPUT_KEY_MEDIA_BACKUP_FILE,
                'm',
                InputOption::VALUE_REQUIRED,
                'Basename of the media backup file in var/backups'
            ),
            new InputOption(
                self::INPUT_KEY_DB_BACKUP_FILE,
                'd',
                InputOption::VALUE_REQUIRED,
                'Basename of the db backup file in var/backups'
            ),
        ];
        $this->setName(self::NAME)
            ->setDescription('Rolls back Magento Application codebase, media and database')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable() && ($input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE)
                || $input->getOption(self::INPUT_KEY_DB_BACKUP_FILE))
        ) {
            $output->writeln("<info>No information is available: the Magento application is not installed.</info>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        return $this->maintenanceModeEnabler->executeInMaintenanceMode(
            function () use ($input, $output) {
                try {
                    $helper = $this->getHelper('question');
                    $question = new ConfirmationQuestion(
                        '<info>You are about to remove current code and/or database tables. Are you sure?[y/N]<info>',
                        false
                    );
                    if (!$helper->ask($input, $output, $question) && $input->isInteractive()) {
                        return \Magento\Framework\Console\Cli::RETURN_FAILURE;
                    }
                    $questionKeep = new ConfirmationQuestion(
                        '<info>Do you want to keep the backups?[y/N]<info>',
                        false
                    );
                    $keepSourceFile = $helper->ask($input, $output, $questionKeep);

                    $this->doRollback($input, $output, $keepSourceFile);
                    $output->writeln('<info>Please set file permission of bin/magento to executable</info>');

                    return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
                } catch (\Exception $e) {
                    $output->writeln('<error>' . $e->getMessage() . '</error>');
                    // we must have an exit code higher than zero to indicate something was wrong
                    return \Magento\Framework\Console\Cli::RETURN_FAILURE;
                }
            },
            $output,
            false
        );
    }

    /**
     * Check rollback options and rolls back appropriately
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param boolean $keepSourceFile
     * @return void
     * @throws \InvalidArgumentException
     */
    private function doRollback(InputInterface $input, OutputInterface $output, $keepSourceFile)
    {
        $inputOptionProvided = false;
        $rollbackHandler = $this->backupRollbackFactory->create($output);
        if ($input->getOption(self::INPUT_KEY_CODE_BACKUP_FILE)) {
            $rollbackHandler->codeRollback(
                $input->getOption(self::INPUT_KEY_CODE_BACKUP_FILE),
                Factory::TYPE_FILESYSTEM,
                $keepSourceFile
            );
            $inputOptionProvided = true;
        }
        if ($input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE)) {
            $rollbackHandler->codeRollback(
                $input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE),
                Factory::TYPE_MEDIA,
                $keepSourceFile
            );
            $inputOptionProvided = true;
        }
        if ($input->getOption(self::INPUT_KEY_DB_BACKUP_FILE)) {
            $this->setAreaCode();
            $rollbackHandler->dbRollback($input->getOption(self::INPUT_KEY_DB_BACKUP_FILE), $keepSourceFile);
            $inputOptionProvided = true;
        }
        if (!$inputOptionProvided) {
            throw new \InvalidArgumentException(
                'Not enough information provided to roll back.'
            );
        }
    }

    /**
     * Sets area code to start a session for database backup and rollback
     *
     * @return void
     */
    private function setAreaCode()
    {
        $areaCode = 'adminhtml';
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\Framework\App\State::class);
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
        $configLoader = $this->objectManager->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class);
        $this->objectManager->configure($configLoader->load($areaCode));
    }
}
