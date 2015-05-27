<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\ConsoleLogger;
use Symfony\Component\Console\Input\InputOption;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\MaintenanceMode;
use Magento\Setup\Model\BackupRollback;
use Magento\Framework\App\DeploymentConfig;

/**
 * Command to rollback code and DB
 */
class RollbackCommand extends AbstractSetupCommand
{
    /**
     * Name of input arguments or options
     */
    const INPUT_KEY_CODE_ROLLBACK = 'code';

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
     * @var BackupRollback
     */
    private $backupRollback;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode
     * @param BackupRollback $backupRollback
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        BackupRollback $backupRollback,
        DeploymentConfig $deploymentConfig
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->maintenanceMode = $maintenanceMode;
        $this->backupRollback = $backupRollback;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $inputOptions = [
            new InputOption(
                self::INPUT_KEY_CODE_ROLLBACK,
                'c',
                InputOption::VALUE_REQUIRED,
                'Rollback code. Value is the backup filename without path.'
            ),
        ];
        $this->setName('setup:rollback')
            ->setDescription('Rollbacks Magento Application code base or database')
            ->setDefinition($inputOptions);
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
        try {
            $output->writeln('<info>Enabling maintenance mode</info>');
            $this->maintenanceMode->set(true);
            if ($input->getOption(self::INPUT_KEY_CODE_ROLLBACK)) {
                $this->backupRollback->codeRollback(
                    $this->objectManager,
                    new ConsoleLogger($output),
                    $input->getOption(self::INPUT_KEY_CODE_ROLLBACK)
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
