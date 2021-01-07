<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Interface for bulk exchange.
 *
 * @api
 * @since 103.0.0
 */
interface ExchangeInterface
{
    /**
     * Send messages in bulk to the queue.
     *
     * @param string $topic
     * @param \Magento\Framework\MessageQueue\EnvelopeInterface[] $envelopes
     * @return mixed
     * @since 103.0.0
     */
    public function enqueue($topic, array $envelopes);
}
