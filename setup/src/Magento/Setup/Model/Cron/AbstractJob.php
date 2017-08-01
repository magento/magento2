<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Cache;
use Magento\Framework\ObjectManagerInterface;

/**
 * Abstract class for jobs run by setup:cron:run command
 * @since 2.0.0
 */
abstract class AbstractJob
{
    /**
     * @var \Magento\Setup\Console\Command\AbstractSetupCommand
     * @since 2.0.0
     */
    protected $command;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     * @since 2.0.0
     */
    protected $output;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $params;

    /**
     * @var \Magento\Framework\App\Cache
     * @since 2.0.0
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles
     * @since 2.0.0
     */
    protected $cleanupFiles;

    /**
     * @var Status
     * @since 2.0.0
     */
    protected $status;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
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
     * @since 2.0.0
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
        $this->cleanupFiles = $this->objectManager->get(\Magento\Framework\App\State\CleanupFiles::class);
        $this->cache = $this->objectManager->get(\Magento\Framework\App\Cache::class);
    }

    /**
     * Get job name.
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get string representation of a job.
     *
     * @return string
     * @since 2.0.0
     */
    public function __toString()
    {
        return $this->name . ' ' . json_encode($this->params, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Do the cleanup
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract public function execute();
}
