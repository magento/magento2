<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Cron\Status;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronRunCommand extends AbstractSetupCommand
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Status
     */
    protected $status;

    /**
     * Constructor
     *
     * @param Queue $queue
     * @param Status $status
     */
    public function __construct(
        Queue $queue,
        Status $status
    ) {
        $this->queue = $queue;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->status->isUpdateInProgress() || $this->status->isUpdateError()) {
            return;
        }
        try {
            $this->status->toggleUpdateInProgress();
        } catch (\RuntimeException $e) {
            $this->status->add($e->getMessage());
            exit();
        }

        try {
            while (!empty($this->queue->peek()) && strpos($this->queue->peek()[Queue::KEY_JOB_NAME], 'setup:') === 0) {
                $job = $this->queue->popQueuedJob();
                $this->status->add(
                    sprintf('Job "%s" has been started', $job)
                );
                try {
                    $job->execute();
                    $this->status->add(sprintf('Job "%s" has been successfully completed', $job));
                } catch (\Exception $e) {
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
}
