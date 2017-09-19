<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

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
    const INPUT_KEY_CODE_BACKUP_FILE = 'code-file';
    const INPUT_KEY_MEDIA_BACKUP_FILE = 'media-file';
    const INPUT_KEY_DB_BACKUP_FILE = 'db-file';

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

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
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        DeploymentConfig $deploymentConfig
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->maintenanceMode = $maintenanceMode;
        $this->backupRollbackFactory = $this->objectManager->get(\Magento\Framework\Setup\BackupRollbackFactory::class);
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
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
        $this->setName('setup:rollback')
            ->setDescription('Rolls back Magento Application codebase, media and database')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable() && ($input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE)
                || $input->getOption(self::INPUT_KEY_DB_BACKUP_FILE))) {
            $output->writeln("<info>No information is available: the Magento application is not installed.</info>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        try {
            $output->writeln('<info>Enabling maintenance mode</info>');
            $this->maintenanceMode->set(true);
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                '<info>You are about to remove current code and/or database tables. Are you sure?[y/N]<info>',
                false
            );
            if (!$helper->ask($input, $output, $question) && $input->isInteractive()) {
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
            $this->doRollback($input, $output);
            $output->writeln('<info>Please set file permission of bin/magento to executable</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
        return $returnValue;
    }

    /**
     * Check rollback options and rolls back appropriately
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \InvalidArgumentException
     */
    private function doRollback(InputInterface $input, OutputInterface $output)
    {
        $inputOptionProvided = false;
        $rollbackHandler = $this->backupRollbackFactory->create($output);
        if ($input->getOption(self::INPUT_KEY_CODE_BACKUP_FILE)) {
            $rollbackHandler->codeRollback($input->getOption(self::INPUT_KEY_CODE_BACKUP_FILE));
            $inputOptionProvided = true;
        }
        if ($input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE)) {
            $rollbackHandler->codeRollback($input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE), Factory::TYPE_MEDIA);
            $inputOptionProvided = true;
        }
        if ($input->getOption(self::INPUT_KEY_DB_BACKUP_FILE)) {
            $this->setAreaCode();
            $rollbackHandler->dbRollback($input->getOption(self::INPUT_KEY_DB_BACKUP_FILE));
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
