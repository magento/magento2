<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Cron\ReadinessCheck;
use Magento\Setup\Model\Cron\Status;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run scheduled jobs, this command should be run as a cron job
 */
class CronRunCommand extends AbstractSetupCommand
{
    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var ReadinessCheck
     */
    protected $readinessCheck;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param Queue $queue
     * @param ReadinessCheck $readinessCheck
     * @param Status $status
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        Queue $queue,
        ReadinessCheck $readinessCheck,
        Status $status
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->queue = $queue;
        $this->readinessCheck = $readinessCheck;
        $this->status = $status;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('setup:cron:run')
            ->setDescription('Runs cron job scheduled for setup application');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notification = 'setup-cron: Please check var/log/update.log for execution summary.';

        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln($notification);
            $this->status->add('Magento is not installed.', \Psr\Log\LogLevel::INFO);
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        }

        if (!$this->checkRun()) {
            $output->writeln($notification);
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        try {
            $this->status->toggleUpdateInProgress();
        } catch (\RuntimeException $e) {
            $this->status->add($e->getMessage(), \Psr\Log\LogLevel::ERROR);
            $output->writeln($notification);
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $returnCode = $this->executeJobsFromQueue();
        if ($returnCode != \Magento\Framework\Console\Cli::RETURN_SUCCESS) {
            $output->writeln($notification);
        }

        return $returnCode;
    }

    /**
     * Check if Cron job can be run
     *
     * @return bool
     */
    private function checkRun()
    {
        return $this->readinessCheck->runReadinessCheck()
        && !$this->status->isUpdateInProgress()
        && !$this->status->isUpdateError();
    }

    /**
     * Executes setup jobs from the queue
     *
     * @return int
     */
    private function executeJobsFromQueue()
    {
        $returnCode = \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        try {
            while (!empty($this->queue->peek()) && strpos($this->queue->peek()[Queue::KEY_JOB_NAME], 'setup:') === 0) {
                $job = $this->queue->popQueuedJob();
                $this->status->add(
                    sprintf('Job "%s" has started' . PHP_EOL, $job),
                    \Psr\Log\LogLevel::INFO
                );
                try {
                    $job->execute();
                    $this->status->add(
                        sprintf('Job "%s" has been successfully completed', $job),
                        \Psr\Log\LogLevel::INFO
                    );
                } catch (\Exception $e) {
                    $this->status->toggleUpdateError(true);
                    $this->status->add(
                        sprintf('An error occurred while executing job "%s": %s', $job, $e->getMessage()),
                        \Psr\Log\LogLevel::ERROR
                    );
                    $returnCode = \Magento\Framework\Console\Cli::RETURN_FAILURE;
                }
            }
        } catch (\Exception $e) {
            $this->status->add($e->getMessage(), \Psr\Log\LogLevel::ERROR);
            $this->status->toggleUpdateError(true);
            $returnCode = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        } finally {
            $this->status->toggleUpdateInProgress(false);
        }
        return $returnCode;
    }
}
