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
class PublisherConnection implements PublisherConnectionInterface
{
    /**
     * Connection name.
     *
     * @var string
     * @since 2.2.0
     */
    private $name;

    /**
     * Exchange name.
     *
     * @var string
     * @since 2.2.0
     */
    private $exchange;

    /**
     * Flag. Is connection disabled.
     *
     * @var bool
     * @since 2.2.0
     */
    private $isDisabled;

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setData(array $data)
    {
        $this->name = $data['name'];
        $this->exchange = $data['exchange'];
        $this->isDisabled = $data['disabled'];
    }
}
