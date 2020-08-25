<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\Framework\App\DeploymentConfig;

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
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param PoisonPillReadInterface $poisonPillRead
     * @param PoisonPillCompareInterface $poisonPillCompare
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        PoisonPillReadInterface $poisonPillRead,
        PoisonPillCompareInterface $poisonPillCompare,
        DeploymentConfig $deploymentConfig
    ) {
        $this->poisonPillRead = $poisonPillRead;
        $this->poisonPillCompare = $poisonPillCompare;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Run short running process
     *
     * @param QueueInterface $queue
     * @param int $maxNumberOfMessages
     * @param \Closure $callback
     * @param int|null $maxIdleTime
     * @param int|null $sleep
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function invoke(
        QueueInterface $queue,
        $maxNumberOfMessages,
        $callback,
        $maxIdleTime = null,
        $sleep = null
    ) {
        $this->poisonPillVersion = $this->poisonPillRead->getLatestVersion();
        $sleep = (int) $sleep ?: 1;
        $maxIdleTime = $maxIdleTime ? (int) $maxIdleTime : PHP_INT_MAX;
        for ($i = $maxNumberOfMessages; $i > 0; $i--) {
            $idleStartTime = microtime(true);
            do {
                $message = $queue->dequeue();
                if (!$message && microtime(true) - $idleStartTime > $maxIdleTime) {
                    break 2;
                }
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
            } while ($message === null && $this->isWaitingNextMessage() && (sleep($sleep) === 0));

            if ($message === null) {
                break;
            }

            if (false === $this->poisonPillCompare->isLatestVersion($this->poisonPillVersion)) {
                $queue->reject($message);
                // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
                exit(0);
            }

            $callback($message);
        }
    }

    /**
     * Checks if consumers should wait for message from the queue
     *
     * @return bool
     */
    private function isWaitingNextMessage(): bool
    {
        return $this->deploymentConfig->get('queue/consumers_wait_for_messages', 1) === 1;
    }
}
