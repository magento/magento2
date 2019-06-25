<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface message Exchange
 *
 * @api
 * @since 102.0.2
 * @since 100.0.2
 */
interface ExchangeInterface
{
    /**
     * Send message
     *
     * @param string $topic
     * @param EnvelopeInterface $envelope
     * @return mixed
     * @since 102.0.2
     */
    public function enqueue($topic, EnvelopeInterface $envelope);
}
