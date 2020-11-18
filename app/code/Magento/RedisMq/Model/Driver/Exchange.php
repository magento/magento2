<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RedisMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;

/**
 * Class Exchange
 */
class Exchange implements ExchangeInterface
{
    /**
     * @var Bulk\Exchange
     */
    private $exchange;

    /**
     * Exchange constructor.
     *
     * @param Bulk\Exchange $exchange
     */
    public function __construct(Bulk\Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * Send message
     *
     * @param string $topic
     * @param EnvelopeInterface $envelope
     * @return mixed
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {
        return $this->exchange->enqueue($topic, [$envelope]);
    }
}
