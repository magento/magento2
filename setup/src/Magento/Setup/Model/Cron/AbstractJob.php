<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Cache;
use Magento\Framework\ObjectManagerInterface;

/**
 * Abstract class for jobs run by setup:cron:run command
 */
abstract class AbstractJob
{
    /**
     * @var \Magento\Setup\Console\Command\AbstractSetupCommand
     */
    protected $command;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var \Magento\Framework\App\Cache
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles
     */
    protected $cleanupFiles;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Magento\Setup\Model\Cron\Status $status
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param string $name
     * @param array $params
     */
    public function __construct(
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Magento\Setup\Model\Cron\Status $status,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        $name,
        array $params = []
    ) {
        $this->output = $output;
        $this->status = $status;
        $this->name = $name;
        $this->params = $params;

        $this->objectManager = $objectManagerProvider->get();
        $this->cleanupFiles = $this->objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $this->cache = $this->objectManager->get('Magento\Framework\App\Cache');
    }

    /**
     * Get job name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get string representation of a job.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name . ' ' . json_encode($this->params, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Do the cleanup
     *
     * @return void
     */
    protected function performCleanup()
    {
        $this->status->add('Cleaning generated files...', \Psr\Log\LogLevel::INFO);
        $this->cleanupFiles->clearCodeGeneratedFiles();
        $this->status->add('Complete!', \Psr\Log\LogLevel::INFO);
        $this->status->add('Clearing cache...', \Psr\Log\LogLevel::INFO);
        $this->cache->clean();
        $this->status->add('Complete!', \Psr\Log\LogLevel::INFO);
    }

    /**
     * Execute job
     *
     * @return void
     */
    abstract public function execute();
}
