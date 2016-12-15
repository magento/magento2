<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Console\Command;

use Magento\Framework\Crontab\CrontabManagerInterface;
use Magento\Framework\Crontab\TasksProviderInterface;
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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:install')
            ->setDescription('Generates and installs crontab for current user')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force install tasks');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->crontabManager->getTasks() && !$input->getOption('force')) {
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
