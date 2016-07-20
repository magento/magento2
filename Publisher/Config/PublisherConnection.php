<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Representation of publisher connection configuration.
 */
class PublisherConnection implements PublisherConnectionInterface
{
    /**
     * Connection name.
     *
     * @var string
     */
    private $name;

    /**
     * Exchange name.
     *
     * @var string
     */
    private $exchange;

    /**
     * Flag. Is connection disabled.
     *
     * @var bool
     */
    private $disabled;

    /**
     * Initialize dependencies.
     *
     * @param string $name
     * @param string $exchange
     * @param bool $disabled
     */
    public function __construct($name, $exchange, $disabled)
    {
        $this->name = $name;
        $this->exchange = $exchange;
        $this->disabled = $disabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
}
