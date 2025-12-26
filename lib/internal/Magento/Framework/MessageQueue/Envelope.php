<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\EnvelopeInterface;

class Envelope implements EnvelopeInterface
{
    /**
     * @var array
     */
    private $properties;

    /**
     * @var string
     */
    private $body;

    /**
     * @param string $body
     * @param array $properties
     */
    public function __construct($body, array $properties = [])
    {
        $this->body = $body;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
