<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Representation of publisher connection configuration.
 * @api
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
