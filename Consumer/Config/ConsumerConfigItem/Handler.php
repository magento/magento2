<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class Handler implements HandlerInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $type;

    /**
     * @var string
     * @since 2.2.0
     */
    private $method;

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setData(array $data)
    {
        $this->type = $data['type'];
        $this->method = $data['method'];
    }
}
