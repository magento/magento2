<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfig;

class QueueResolver
{
    /**
     * @param PublisherConfig $publisherConfig
     * @param TopologyConfig $topologyConfig
     */
    public function __construct(
        private readonly PublisherConfig $publisherConfig,
        private readonly TopologyConfig $topologyConfig,
    ) {
    }

    /**
     * Get queue name by topic.
     *
     * @param string $topic
     * @return string
     */
    public function getByTopic(string $topic): string
    {
        $queue = $topic;

        $publisherConnection = $this->publisherConfig->getPublisher($topic)->getConnection();
        $exchange = $this->topologyConfig->getExchange(
            $publisherConnection->getExchange(),
            $publisherConnection->getName()
        );
        foreach ($exchange->getBindings() as $binding) {
            if ($this->isMatch($binding->getTopic(), $topic)) {
                $queue = $binding->getDestination();
                break;
            }
        }

        return $queue;
    }

    /**
     * Check if topic matches routing key.
     *
     * Takes into account AMQP topic matching semantics:
     * - tokens separated by '.'
     * - '*' matches exactly one token
     * - '#' matches zero or more tokens
     *
     * @param string $routingKey
     * @param string $topic
     * @return bool
     */
    private function isMatch(string $routingKey, string $topic): bool
    {
        if ($routingKey === $topic) {
            return true;
        }

        if ($this->hasNoWildcards($routingKey)) {
            return false;
        }

        $patternParts = explode('.', $routingKey);
        $keyParts = explode('.', $topic);

        return $this->matchTopicParts($patternParts, $keyParts);
    }

    /**
     * Check if routing key contains no wildcards.
     *
     * @param string $routingKey
     * @return bool
     */
    private function hasNoWildcards(string $routingKey): bool
    {
        return !str_contains($routingKey, '*') && !str_contains($routingKey, '#');
    }

    /**
     * Match routing key/topic parts using AMQP topic semantics.
     *
     * @param string[] $patternParts
     * @param string[] $keyParts
     * @return bool
     */
    private function matchTopicParts(array $patternParts, array $keyParts): bool
    {
        $pCount = count($patternParts);
        $kCount = count($keyParts);
        $p = $k = 0;
        // For backtracking when we see '#'.
        $hashPatternIndex = $hashKeyIndex = -1;

        while ($k < $kCount) {
            $part = $p < $pCount ? $patternParts[$p] : null;

            if ($part === '#') {
                // Remember where '#' is and try to match zero tokens first.
                $hashPatternIndex = $p;
                $hashKeyIndex = $k;
                $p++;
                continue;
            }

            if ($part !== null && ($part === '*' || $part === $keyParts[$k])) {
                $p++;
                $k++;
                continue;
            }

            if ($hashPatternIndex === -1) {
                return false;
            }

            // Mismatch: expand previous '# ' by one token and retry.
            $hashKeyIndex++;
            $k = $hashKeyIndex;
            $p = $hashPatternIndex + 1;
        }

        $p = $this->consumeTrailingHashTokens($patternParts, $p);

        return $p === $pCount;
    }

    /**
     * Consume trailing '#' tokens (they can match an empty suffix).
     *
     * @param string[] $patternParts
     * @param int $p
     * @return int
     */
    private function consumeTrailingHashTokens(array $patternParts, int $p): int
    {
        $pCount = count($patternParts);
        while ($p < $pCount && $patternParts[$p] === '#') {
            $p++;
        }

        return $p;
    }
}
