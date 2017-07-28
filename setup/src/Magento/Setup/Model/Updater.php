<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Filesystem;
use Magento\Setup\Model\Cron\Queue;

/**
 * Class Updater passes information to the updater application
 * @since 2.0.0
 */
class Updater
{
    /**#@+
     * Task types
     */
    const TASK_TYPE_UPDATE = 'update';
    const TASK_TYPE_UNINSTALL = 'uninstall';
    const TASK_TYPE_MAINTENANCE_MODE = 'maintenance_mode';
    /**#@-*/

    /**
     * @var Queue
     * @since 2.0.0
     */
    private $queue;

    /**
     * Constructor
     *
     * @param Queue $queue
     * @since 2.0.0
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Create an update task for Updater app
     *
     * @param array $packages
     * @param string $type
     * @param array $additionalOptions
     * @return string
     * @since 2.0.0
     */
    public function createUpdaterTask(array $packages, $type, array $additionalOptions = [])
    {
        try {
            // write to .update_queue.json file
            $params = [];
            if (!empty($packages)) {
                $params['components'] = $packages;
            }
            foreach ($additionalOptions as $key => $value) {
                $params[$key] = $value;
            }

            $this->queue->addJobs([['name' => $type, 'params' => $params]]);
            return '';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
