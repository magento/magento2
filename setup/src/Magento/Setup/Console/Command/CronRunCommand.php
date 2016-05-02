<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     *  Cron execution return codes
     */
    const SETUP_CRON_NORMAL_EXIT = 0;
    // 1 & 2 are reserved codes
    const SETUP_CRON_READINESS_CHECK_FAILURE = 3;
    const SETUP_CRON_START_UPDATE_ERROR = 4;
    const SETUP_CRON_UPDATE_JOB_ERROR = 5;

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
        $notification = 'setup-cron: Please check var/log/update.log for errors.';
        if (!$this->checkRun()) {
            $output->writeln($notification);
            return self::SETUP_CRON_READINESS_CHECK_FAILURE;
        }
        try {
            $this->status->toggleUpdateInProgress();
        } catch (\RuntimeException $e) {
            $this->status->add($e->getMessage(), \Magento\Framework\Logger\Monolog::ERROR);
            $output->writeln($notification);
            return self::SETUP_CRON_START_UPDATE_ERROR;
        }

        $returnCode = self::SETUP_CRON_NORMAL_EXIT;
        try {
            while (!empty($this->queue->peek()) && strpos($this->queue->peek()[Queue::KEY_JOB_NAME], 'setup:') === 0) {
                $job = $this->queue->popQueuedJob();
                $this->status->add(
                    sprintf('Job "%s" has started' . PHP_EOL, $job),
                    \Magento\Framework\Logger\Monolog::INFO
                );
                try {
                    $job->execute();
                    $this->status->add(
                        sprintf('Job "%s" has been successfully completed', $job),
                        \Magento\Framework\Logger\Monolog::INFO
                    );
                } catch (\Exception $e) {
                    $this->status->toggleUpdateError(true);
                    $this->status->add(
                        sprintf('An error occurred while executing job "%s": %s', $job, $e->getMessage()),
                        \Magento\Framework\Logger\Monolog::ERROR
                    );
                    $returnCode = self::SETUP_CRON_UPDATE_JOB_ERROR;
                }
            }
        } catch (\Exception $e) {
            $this->status->add($e->getMessage(), \Magento\Framework\Logger\Monolog::ERROR);
            $this->status->toggleUpdateError(true);
            $returnCode = self::SETUP_CRON_UPDATE_JOB_ERROR;
        } finally {
            $this->status->toggleUpdateInProgress(false);
            if ($returnCode != self::SETUP_CRON_NORMAL_EXIT) {
                $output->writeln($notification);
            }
            return $returnCode;
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
