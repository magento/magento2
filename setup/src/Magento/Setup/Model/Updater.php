<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Cron\Queue\Reader;

/**
 * Class Updater passes information to the updater application
 */
class Updater
{
    /**
     * Path to Magento var directory
     *
     * @var string
     */
    private $queueFilePath;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $write;

    private $queue;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, Queue $queue)
    {
        $this->queueFilePath = '.update_queue.json';
        $this->write = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->write->touch($this->queueFilePath);
        $this->queue = $queue;
    }

    /**
     * Create an update task for Updater app
     *
     * @param array $packages
     * @return string
     */
    public function createUpdaterTask($packages)
    {
        try {
            // write to .update_queue.json file
            $this->queue->addJobs([['name' => 'update', 'params' => ['require' => $packages]]]);
            return '';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
