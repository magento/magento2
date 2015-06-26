<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Filesystem;
use Magento\Setup\Model\Cron\Queue;

/**
 * Class Updater passes information to the updater application
 */
class Updater
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * Constructor
     *
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
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
