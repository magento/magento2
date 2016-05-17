<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Symfony\Component\Console\Input\ArrayInput;

class JobSetCache extends AbstractJob
{
    /**
     * @var \Magento\Backend\Console\Command\AbstractCacheSetCommand
     */
    protected $command;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @param \Magento\Backend\Console\Command\AbstractCacheSetCommand $command
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        \Magento\Backend\Console\Command\AbstractCacheSetCommand $command,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Magento\Setup\Model\Cron\Status $status,
        $name,
        $params = []
    ) {
        $this->command = $command;
        $this->output = $output;
        $this->status = $status;
        parent::__construct($output, $status, $objectManagerProvider, $name, $params);
    }

    /**
     * Execute set cache comand
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->command->run(new ArrayInput(['command' => $this->command->getName()]), $this->output);
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }
}
