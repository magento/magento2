<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Console\Command;

use Magento\Framework\Crontab\CrontabManagerInterface;
use Magento\Framework\Crontab\TasksProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputOption;

/**
 * CronInstallCommand installs Magento cron tasks
 */
class CronInstallCommand extends Command
{
    private const COMMAND_OPTION_FORCE = 'force';
    private const COMMAND_OPTION_NON_OPTIONAL = 'non-optional';

    /**
     * @var CrontabManagerInterface
     */
    private $crontabManager;

    /**
     * @var TasksProviderInterface
     */
    private $tasksProvider;

    /**
     * @param CrontabManagerInterface $crontabManager
     * @param TasksProviderInterface $tasksProvider
     */
    public function __construct(
        CrontabManagerInterface $crontabManager,
        TasksProviderInterface $tasksProvider
    ) {
        $this->crontabManager = $crontabManager;
        $this->tasksProvider = $tasksProvider;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('cron:install')
            ->setDescription('Generates and installs crontab for current user')
            ->addOption(self::COMMAND_OPTION_FORCE, 'f', InputOption::VALUE_NONE, 'Force install tasks')
            // @codingStandardsIgnoreStart
            ->addOption(self::COMMAND_OPTION_NON_OPTIONAL, 'd', InputOption::VALUE_NONE, 'Install only the non-optional (default) tasks');
            // @codingStandardsIgnoreEnd

        parent::configure();
    }

    /**
     * Executes "cron:install" command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->crontabManager->getTasks() && !$input->getOption('force')) {
            $output->writeln('<error>Crontab has already been generated and saved</error>');
            return Cli::RETURN_FAILURE;
        }

        $tasks = $this->tasksProvider->getTasks();
        if ($input->getOption(self::COMMAND_OPTION_NON_OPTIONAL)) {
            $tasks = $this->extractNonOptionalTasks($tasks);
        }

        try {
            $this->crontabManager->saveTasks($tasks);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Crontab has been generated and saved</info>');

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Returns an array of non-optional tasks
     *
     * @param array $tasks
     * @return array
     */
    private function extractNonOptionalTasks(array $tasks = []): array
    {
        $defaultTasks = [];

        foreach ($tasks as $taskCode => $taskParams) {
            if (!$taskParams['optional']) {
                $defaultTasks[$taskCode] = $taskParams;
            }
        }

        return $defaultTasks;
    }
}
