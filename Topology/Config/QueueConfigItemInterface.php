<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Instances of this class represent queue config items.
 * @since 2.2.0
 */
interface QueueConfigItemInterface
{
    /**
     * Get queue name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getName();

    /**
     * Get queue connection.
     *
     * @return string
     * @since 2.2.0
     */
    public function getConnection();

    /**
     * Check if queue is durable.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isDurable();

    /**
     * Check if queue is auto delete.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isAutoDelete();

    /**
     * Get queue arguments
     *
     * @return array
     * @since 2.2.0
     */
    public function getArguments();
}
