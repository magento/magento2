<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\Stomp\Exception\ClientException;
use Stomp\Transport\Frame;
use Stomp\Transport\Message;

interface StompClientInterface
{
    /**
     * Push message to queue/topic
     *
     * @param string $queue
     * @param Message $message
     * @return void
     * @throws ClientException
     */
    public function send(string $queue, Message $message): void;

    /**
     * Push messages to queue/topic
     *
     * @param string $queue
     * @param Message[] $messages
     * @return void
     * @throws ClientException
     */
    public function sendBatch(string $queue, array $messages): void;

    /**
     * Subscribe queue
     *
     * @param string $queue
     * @return void
     * @throws ClientException
     */
    public function subscribeQueue(string $queue): void;

    /**
     * Unsubscribe queue
     *
     * @param string $queue
     * @return void
     */
    public function unsubscribeQueue(string $queue): void;

    /**
     * Read message from queue
     *
     * @param string $queue
     * @return Frame|null
     * @throws ClientException
     */
    public function readMessage(string $queue): ?Frame;

    /**
     * Acknowledge message from queue
     *
     * @param Frame $lastFrame
     * @return void
     * @throws ClientException
     */
    public function ackMessage(Frame $lastFrame): void;

    /**
     * Reject message from queue
     *
     * @param Frame $lastFrame
     * @param bool|null $requeue
     * @return void
     * @throws ClientException
     */
    public function nackMessage(Frame $lastFrame, ?bool $requeue = null): void;
}
