<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

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
     */
    public function send(string $queue, Message $message): void;

    /**
     * Subscribe queue
     *
     * @param string $queue
     * @return void
     */
    public function subscribeQueue(string $queue): void;

    /**
     * Read message from queue
     *
     * @return Frame|null
     */
    public function readMessage(): ?Frame;

    /**
     * Acknowledge message from queue
     *
     * @param Frame $lastFrame
     * @return void
     */
    public function ackMessage(Frame $lastFrame): void;

    /**
     * Reject message from queue
     *
     * @param Frame $lastFrame
     * @return void
     */
    public function nackMessage(Frame $lastFrame): void;

    /**
     * Read message frame
     *
     * @return Frame
     */
    public function readFrame(): Frame;
}
