<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Console\Command\AbstractSetupCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\Cron\Queue;

/**
 * Upgrade job
 */
class JobUpgrade extends AbstractJob
{
    /**
     * @var \Magento\Setup\Model\Cron\Status
     */
    protected $status;

    /**
     * @var \Magento\Setup\Model\Cron\Queue
     */
    private $queue;

    /**
     * Constructor
     *
     * @param \Magento\Setup\Console\Command\AbstractSetupCommand $command
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Magento\Setup\Model\Cron\Queue $queue
     * @param \Magento\Setup\Model\Cron\Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        \Magento\Setup\Console\Command\AbstractSetupCommand $command,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Magento\Setup\Model\Cron\Queue $queue,
        \Magento\Setup\Model\Cron\Status $status,
        $name,
        $params = []
    ) {
        $this->command = $command;
        $this->output = $output;
        $this->status = $status;
        $this->queue = $queue;
        parent::__construct($output, $status, $objectManagerProvider, $name, $params);
    }

    /**
     * Execute job
     *
     * @throws \RuntimeException
     * @return void
     */
    public function execute()
    {
        try {
            $this->queue->addJobs(
                [['name' => JobFactory::JOB_STATIC_REGENERATE, 'params' => []]]
            );
            $this->queue->addJobs(
                [['name' => \Magento\Setup\Model\Updater::TASK_TYPE_MAINTENANCE_MODE, 'params' => ['enable' => false]]]
            );
            $this->params['command'] = 'setup:upgrade';
            $this->command->run(new ArrayInput($this->params), $this->output);
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }
}
