<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var string
     */
    private $messageId;

    /**
     * @param string $body
     * @param array $properties
     */
    public function __construct(\Magento\Framework\Json\DecoderInterface $jsonDecoder, $body, array $properties = [])
    {
        $data = $jsonDecoder->decode($body);
        $this->body = $body;
        $this->properties = $properties;
        $this->messageId = $data['message_id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageId()
    {
        return $this->messageId;
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
