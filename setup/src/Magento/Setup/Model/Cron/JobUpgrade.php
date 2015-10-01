<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var Status
     */
    protected $status;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * Constructor
     *
     * @param AbstractSetupCommand $command
     * @param OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        AbstractSetupCommand $command,
        ObjectManagerProvider $objectManagerProvider,
        OutputInterface $output,
        Queue $queue,
        Status $status,
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
            $this->params['command'] = 'setup:upgrade';
            $this->command->run(new ArrayInput($this->params), $this->output);
            $this->queue->addJobs(
                [['name' => JobFactory::JOB_STATIC_REGENERATE, 'params' => []]]
            );
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }
}
