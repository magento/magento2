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

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->name = $data['name'];
        $this->exchange = $data['exchange'];
        $this->disabled = $data['disabled'];
    }
}
