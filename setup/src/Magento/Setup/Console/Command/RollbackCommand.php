<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\ConsoleLogger;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\MaintenanceMode;
use Magento\Setup\Model\BackupRollback;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Command to rollback code and DB
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RollbackCommand extends AbstractSetupCommand
{
    /**
     * Name of input arguments or options
     */
    const INPUT_KEY_BACKUP_FILE = 'file';

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
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $file;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->maintenanceMode = $maintenanceMode;
        $this->directoryList = $directoryList;
        $this->file = $file;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $inputArguments = [
            new InputArgument(
                self::INPUT_KEY_BACKUP_FILE,
                InputArgument::REQUIRED,
                'Basename of the backup file in var/backups'
            ),
        ];
        $this->setName('setup:rollback')
            ->setDescription('Rolls back Magento Application code base')
            ->setDefinition($inputArguments);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('<info>Enabling maintenance mode</info>');
            $this->maintenanceMode->set(true);
            $backupRollback = new BackupRollback(
                $this->objectManager,
                new ConsoleLogger($output),
                $this->directoryList,
                $this->file
            );
            $backupRollback->codeRollback($input->getArgument(self::INPUT_KEY_BACKUP_FILE));
            $output->writeln("<info>Please set file permission of 'bin/magento' to executable</info>");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
    }
}
