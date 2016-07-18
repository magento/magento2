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
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var bool
     */
    private $isDisabled;

    /**
     * Initialize dependencies.
     *
     * @param string $name
     * @param string $exchange
     * @param bool $isDisabled
     */
    public function __construct($name, $exchange, $isDisabled)
    {
        $this->name = $name;
        $this->exchange = $exchange;
        $this->isDisabled = $isDisabled;
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
        return $this->isDisabled;
    }
}
