<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\EnvelopeInterface;

/**
 * Class \Magento\Framework\MessageQueue\Envelope
 *
 * @since 2.0.0
 */
class Envelope implements EnvelopeInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $properties;

    /**
     * @var string
     * @since 2.0.0
     */
    private $body;

    /**
     * @param string $body
     * @param array $properties
     * @since 2.0.0
     */
    public function __construct($body, array $properties = [])
    {
        $this->body = $body;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
