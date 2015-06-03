<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Input\InputOption;
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
 * Command to backup code base and user data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupCommand extends AbstractSetupCommand
{
    /**
     * Name of input options
     */

    const INPUT_KEY_CODE = 'code';
    const INPUT_KEY_DATA = 'data';

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
        $this->setDescription('Takes backup of Magento Application code base and user data');
        $this->setName('setup:backup');
        $this->addOption(
            self::INPUT_KEY_CODE,
            null,
            InputOption::VALUE_NONE,
            'Take code backup (excluding temporary files)'
        );
        $this->addOption(
            self::INPUT_KEY_DATA,
            null,
            InputOption::VALUE_NONE,
            'Take complete database and media backup'
        );
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
            if ($input->getOption(self::INPUT_KEY_CODE)) {
                $backupRollback->codeBackup();
            }
            if ($input->getOption(self::INPUT_KEY_DATA)) {
                $backupRollback->dataBackup();
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
    }
}
