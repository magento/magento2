<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->maintenanceMode = $maintenanceMode;
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
            ->setDescription('Rolls back Magento Application code base, media and database')
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
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                '<info>You are about to remove current code and database tables. Are you sure?[y/N]<info>',
                false
            );
            if (!$helper->ask($input, $output, $question) && $input->isInteractive()) {
                return;
            }
            if ($input->getOption(self::INPUT_KEY_CODE_BACKUP_FILE)) {
                $codeRollback = $this->backupRollbackFactory->create($output);
                $codeRollback->codeRollback($input->getOption(self::INPUT_KEY_CODE_BACKUP_FILE));
                $inputOptionProvided = true;
            }
            if ($input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE)) {
                $mediaRollback = $this->backupRollbackFactory->create($output);
                $mediaRollback->codeRollback($input->getOption(self::INPUT_KEY_MEDIA_BACKUP_FILE), Factory::TYPE_MEDIA);
                $inputOptionProvided = true;
            }
            if ($input->getOption(self::INPUT_KEY_DB_BACKUP_FILE)) {
                $dbRollback = $this->backupRollbackFactory->create($output);
                $dbRollback->dbRollback($input->getOption(self::INPUT_KEY_DB_BACKUP_FILE));
                $inputOptionProvided = true;
            }
            if (!$inputOptionProvided) {
                throw new \InvalidArgumentException(
                    'No option is provided for the command to rollback.'
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
