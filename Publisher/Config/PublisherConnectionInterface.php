<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Get is connection disabled.
     *
     * @return bool
     */
    public function isDisabled();

    /**
     * Set connection data.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data);
}
