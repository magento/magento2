<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Console\Command;

use Magento\Framework\Crontab\CrontabManager;
use Magento\Framework\Crontab\TasksProvider;
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
    /**
     * @var CrontabManager
     */
    private $crontabManager;

    /**
     * @var TasksProvider
     */
    private $tasksProvider;

    /**
     * @param CrontabManager $crontabManager
     * @param TasksProvider $tasksProvider
     */
    public function __construct(
        CrontabManager $crontabManager,
        TasksProvider $tasksProvider
    ) {
        $this->crontabManager = $crontabManager;
        $this->tasksProvider = $tasksProvider;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:install')
            ->setDescription('Generates and installs crontab for current user')
            ->addOption('reinstall', 'r', InputOption::VALUE_NONE, 'Reinstall tasks');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->crontabManager->getTasks() && !$input->getOption('reinstall')) {
            $output->writeln('<error>Crontab has already been generated and saved</error>');
            return Cli::RETURN_FAILURE;
        }

        try {
            $this->crontabManager->saveTasks($this->tasksProvider->getTasks());
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Crontab has been generated and saved</info>');

        return Cli::RETURN_SUCCESS;
    }
}
