<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Console\Command;

use Magento\Framework\Crontab\CrontabManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

/**
 * CronRemoveCommand removes Magento cron tasks
 */
class CronRemoveCommand extends Command
{
    /**
     * @var CrontabManager
     */
    private $crontabManager;

    /**
     * @param CrontabManager $crontabManager
     */
    public function __construct(CrontabManager $crontabManager)
    {
        $this->crontabManager = $crontabManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:remove')
            ->setDescription('Removes tasks from crontab');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->crontabManager->removeTasks();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Magento cron tasks have been removed</info>');

        return Cli::RETURN_SUCCESS;
    }
}
