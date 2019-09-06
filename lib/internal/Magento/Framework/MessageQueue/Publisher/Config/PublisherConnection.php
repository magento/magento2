<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    private $isDisabled;

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

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->name = $data['name'];
        $this->exchange = $data['exchange'];
        $this->isDisabled = $data['disabled'];
    }
}
