<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;

/**
 * {@inheritdoc}
 */
class Handler implements HandlerInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $method;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->type = $data['type'];
        $this->method = $data['method'];
    }
}
