<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Mail\Template;

/**
 * Mock of mail transport builder
 */
class TransportBuilderMock extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @var \Magento\Framework\Mail\Message
     */
    protected $_sentMessage;

    /**
     * @var callable
     */
    private $onMessageSentCallback;

    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset()
    {
        $this->_sentMessage = $this->message;
        return parent::reset();
    }

    /**
     * Return message object with prepared data
     *
     * @return \Magento\Framework\Mail\Message|null
     */
    public function getSentMessage()
    {
        return $this->_sentMessage;
    }

    /**
     * Return transport mock.
     *
     * @return \Magento\TestFramework\Mail\TransportInterfaceMock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTransport()
    {
        $this->prepareMessage();
        $this->reset();
        return $this->objectManager->create(
            \Magento\TestFramework\Mail\TransportInterfaceMock::class,
            [
                'message' => $this->message,
                'onMessageSentCallback' => $this->onMessageSentCallback
            ]
        );
    }

    /**
     * Set callback to be called when message is sent.
     *
     * @param callable $callback
     */
    public function setOnMessageSentCallback(callable $callback): void
    {
        $this->onMessageSentCallback = $callback;
    }

    /**
     * Clean previous test data.
     */
    public function clean(): void
    {
        $this->_sentMessage = null;
        $this->onMessageSentCallback = null;
    }
}
