<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Instances of this class represent queue config items.
 * @api
 */
interface QueueConfigItemInterface
{
    /**
     * Get queue name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get queue connection.
     *
     * @return string
     */
    public function getConnection();

    /**
     * Check if queue is durable.
     *
     * @return bool
     */
    public function isDurable();

    /**
     * Check if queue is auto delete.
     *
     * @return bool
     */
    public function isAutoDelete();

    /**
     * Get queue arguments
     *
     * @return array
     */
    public function getArguments();
}
