<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Interface for bulk exchange.
 */
interface ExchangeInterface
{
    /**
     * Send messages in bulk to the queue.
     *
     * @param string $topic
     * @param \Magento\Framework\MessageQueue\EnvelopeInterface[] $envelopes
     * @return mixed
     */
    public function enqueue($topic, array $envelopes);
}
