<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Representation of publisher connection configuration.
 */
interface PublisherConnectionInterface
{
    /**
     * Get Connection name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get exchange name.
     *
     * @return string
     */
    public function getExchange();

    /**
     * Check if connection disabled.
     *
     * @return bool
     */
    public function isDisabled();
}
