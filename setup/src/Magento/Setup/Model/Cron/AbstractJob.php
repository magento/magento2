<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Cache;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Abstract class for jobs run by setup:cron:run command
 */
abstract class AbstractJob
{
    /**
     * @var AbstractSetupCommand
     */
    protected $command;

    /**
     * @var OutputInterface
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
     * Constructor
     *
     * @param OutputInterface $output
     * @param Status $status
     * @param ObjectManagerProvider $objectManagerProvider
     * @param string $name
     * @param array $params
     */
    public function __construct(
        OutputInterface $output,
        Status $status,
        ObjectManagerProvider $objectManagerProvider,
        $name,
        array $params = []
    ) {
        $this->output = $output;
        $this->status = $status;
        $this->name = $name;
        $this->params = $params;

        $objectManager = $objectManagerProvider->get();
        $this->cleanupFiles = $objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $this->cache = $objectManager->get('Magento\Framework\App\Cache');
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
        $this->status->add('Cleaning generated files...');
        $this->cleanupFiles->clearCodeGeneratedFiles();
        $this->status->add('Complete!');
        $this->status->add('Clearing cache...');
        $this->cache->clean();
        $this->status->add('Complete!');
    }

    /**
     * Execute job
     *
     * @return void
     */
    abstract public function execute();
}
