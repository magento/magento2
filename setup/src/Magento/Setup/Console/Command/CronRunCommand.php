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
        if (!$this->checkRun()) {
            return;
        }
        try {
            $this->status->toggleUpdateInProgress();
        } catch (\RuntimeException $e) {
            $this->status->add($e->getMessage());
            return;
        }

        try {
            while (!empty($this->queue->peek()) && strpos($this->queue->peek()[Queue::KEY_JOB_NAME], 'setup:') === 0) {
                $job = $this->queue->popQueuedJob();
                $this->status->add(
                    sprintf('Job "%s" has started' . PHP_EOL, $job)
                );
                try {
                    $job->execute();
                    $this->status->add(sprintf('Job "%s" has been successfully completed', $job));
                } catch (\Exception $e) {
                    $this->status->toggleUpdateError(true);
                    $this->status->add(
                        sprintf('An error occurred while executing job "%s": %s', $job, $e->getMessage())
                    );
                }
            }
        } catch (\Exception $e) {
            $this->status->add($e->getMessage());
            $this->status->toggleUpdateError(true);
        } finally {
            $this->status->toggleUpdateInProgress(false);
        }
    }

    /**
     * Check if Cron job can be run
     *
     * @return bool
     */
    private function checkRun()
    {
        return $this->deploymentConfig->isAvailable()
        && $this->readinessCheck->runReadinessCheck()
        && !$this->status->isUpdateInProgress()
        && !$this->status->isUpdateError();
    }
}
