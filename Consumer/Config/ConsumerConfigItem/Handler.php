<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * Initialize data.
     *
     * @param string $type
     * @param string $method
     */
    public function __construct($type, $method)
    {
        $this->type = $type;
        $this->method = $method;
    }

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
}
