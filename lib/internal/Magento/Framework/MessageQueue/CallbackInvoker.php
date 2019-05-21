<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;

/**
 * Class CallbackInvoker to invoke callbacks for consumer classes
 */
class CallbackInvoker implements CallbackInvokerInterface
{
    /**
     * @var PoisonPillReadInterface $poisonPillRead
     */
    private $poisonPillRead;

    /**
     * @var int $poisonPillVersion
     */
    private $poisonPillVersion;

    /**
     * @var PoisonPillCompareInterface
     */
    private $poisonPillCompare;

    /**
     * @param PoisonPillReadInterface $poisonPillRead
     * @param PoisonPillCompareInterface $poisonPillCompare
     */
    public function __construct(
        PoisonPillReadInterface $poisonPillRead,
        PoisonPillCompareInterface $poisonPillCompare
    ) {
        $this->poisonPillRead = $poisonPillRead;
        $this->poisonPillCompare = $poisonPillCompare;
    }

    /**
     * Run short running process
     *
     * @param QueueInterface $queue
     * @param int $maxNumberOfMessages
     * @param \Closure $callback
     * @return void
     */
    public function invoke(QueueInterface $queue, $maxNumberOfMessages, $callback)
    {
        $this->poisonPillVersion = $this->poisonPillRead->getLatestVersion();
        for ($i = $maxNumberOfMessages; $i > 0; $i--) {
            do {
                $message = $queue->dequeue();
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
            } while ($message === null && (sleep(1) === 0));
            if (false === $this->poisonPillCompare->isLatestVersion($this->poisonPillVersion)) {
                $queue->reject($message);
                // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
                exit(0);
            }
            $callback($message);
        }
    }
}
