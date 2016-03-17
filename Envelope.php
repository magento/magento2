<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * This getter serves as a workaround to add this dependency to this class without breaking constructor structure.
     *
     * @return \Magento\Framework\Json\DecoderInterface
     *
     * @deprecated
     */
    private function getJsonDecoder()
    {
        if ($this->jsonDecoder === null) {
            $this->jsonDecoder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Json\DecoderInterface');
        }
        return $this->jsonDecoder;
    }

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
    public function getMessageId()
    {
        if ($this->messageId === null) {
            $this->messageId = $this->getJsonDecoder()->decode($this->getBody())['message_id'];
        }
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
