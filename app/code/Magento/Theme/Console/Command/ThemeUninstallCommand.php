<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\BackupRollback;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ConsoleLogger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Command for uninstalling theme and backup-code feature
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThemeUninstallCommand extends Command
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_BACKUP_CODE = 'backup-code';
    const INPUT_KEY_THEMES = 'theme';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @param DeploymentConfig $deploymentConfig
     * @param MaintenanceMode $maintenanceMode
     * @param ObjectManagerInterface $objectManager
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        MaintenanceMode $maintenanceMode,
        ObjectManagerInterface $objectManager,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->maintenanceMode = $maintenanceMode;
        $this->objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->file = $file;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('theme:uninstall');
        $this->setDescription('Uninstall theme');
        $this->addOption(
            self::INPUT_KEY_BACKUP_CODE,
            null,
            InputOption::VALUE_NONE,
            'Take code backup (excluding temporary files)'
        );
        $this->addArgument(
            self::INPUT_KEY_THEMES,
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'Path of the theme'
        );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                '<error>You cannot run this command because the Magento application is not installed.</error>'
            );
            return;
        }

        $output->writeln('<info>Enabling maintenance mode</info>');
        $this->maintenanceMode->set(true);

        try {
            if ($input->getOption(self::INPUT_KEY_BACKUP_CODE)) {
                $backupRollback = new BackupRollback(
                    $this->objectManager,
                    new ConsoleLogger($output),
                    $this->directoryList,
                    $this->file
                );
                $backupRollback->codeBackup();
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
    }
}
