<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Representation of publisher connection configuration.
 * @since 2.2.0
 */
interface PublisherConnectionInterface
{
    /**
     * Get Connection name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getName();

    /**
     * Get exchange name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getExchange();

    /**
     * Check if connection disabled.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isDisabled();
}
