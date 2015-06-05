<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * Command to backup code base and user data
 */
class BackupCommand extends AbstractSetupCommand
{
    /**
     * Name of input options
     */

    const INPUT_KEY_CODE = 'code';
    const INPUT_KEY_MEDIA = 'media';
    const INPUT_KEY_DB = 'db';

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
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

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
        $this->backupRollbackFactory = $this->objectManager->get('Magento\Framework\Setup\BackupRollbackFactory');
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_CODE,
                null,
                InputOption::VALUE_NONE,
                'Take code and configuration files backup (excluding temporary files)'
            ),
            new InputOption(
                self::INPUT_KEY_MEDIA,
                null,
                InputOption::VALUE_NONE,
                'Take media backup'
            ),
            new InputOption(
                self::INPUT_KEY_DB,
                null,
                InputOption::VALUE_NONE,
                'Take complete database backup'
            ),
        ];
        $this->setName('setup:backup')
            ->setDescription('Takes backup of Magento Application code base, media and database')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $inputOptionProvided = false;
            $output->writeln('<info>Enabling maintenance mode</info>');
            $this->maintenanceMode->set(true);
            $time = time();
            if ($input->getOption(self::INPUT_KEY_CODE)) {
                $codeBackup = $this->backupRollbackFactory->create($output);
                $codeBackup->codeBackup($time);
                $inputOptionProvided = true;
            }
            if ($input->getOption(self::INPUT_KEY_MEDIA)) {
                $mediaBackup = $this->backupRollbackFactory->create($output);
                $mediaBackup->codeBackup($time, Factory::TYPE_MEDIA);
                $inputOptionProvided = true;
            }
            if ($input->getOption(self::INPUT_KEY_DB)) {
                $dbBackup = $this->backupRollbackFactory->create($output);
                $dbBackup->dbBackup($time);
                $inputOptionProvided = true;
            }
            if (!$inputOptionProvided) {
                throw new \InvalidArgumentException(
                    'No option is provided for the command to take backup.'
                );
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
    }
}
