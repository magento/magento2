<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManagerInterface;

class MergerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $mergers;

    /**
     * MergerFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param string[] $mergers
     */
    public function __construct(ObjectManagerInterface $objectManager, $mergers)
    {
        $this->objectManager = $objectManager;
        $this->mergers = $mergers;
    }

    /**
     * @param string $consumerName
     * @return MergerInterface
     * @throws \LogicException
     */
    public function create($consumerName)
    {
        $matchMergerWildcard = $this->matchConsumer($consumerName);

        if (!isset($this->mergers[$consumerName]) && !isset($matchMergerWildcard)) {
            throw new \LogicException("Not found merger for consumer name '{$consumerName}'");
        }

        if (isset($this->mergers[$consumerName])) {
            $mergerClassName = $this->mergers[$consumerName];
        } else {
            $mergerClassName = $this->mergers[$matchMergerWildcard];
        }

        $merger = $this->objectManager->get($mergerClassName);

        if (!$merger instanceof MergerInterface) {
            $mergerInterfaceName = \Magento\Framework\MessageQueue\MergerInterface::class;
            throw new \LogicException(
                "Merger '{$mergerClassName}' for consumer name '{$consumerName}' " .
                "does not implement interface '{$mergerInterfaceName}'"
            );
        }

        return $merger;
    }

    /**
     * @param $consumerName
     * @return string|null
     */
    private function matchConsumer($consumerName)
    {
        $patterns = [];
        foreach (array_keys($this->mergers) as $mergerFor) {
            if (strpos($mergerFor, '*') !== false || strpos($mergerFor, '#') !== false) {
                $patterns[$mergerFor] = $this->buildWildcardPattern($mergerFor);
            }
        }

        foreach ($patterns as $mergerKey => $pattern) {
            if (preg_match($pattern, $consumerName)) {
                return $mergerKey;
            }
        }
    }

    /**
     * Construct perl regexp pattern for matching topic names from wildcard key.
     *
     * @param string $wildcardKey
     * @return string
     */
    private function buildWildcardPattern($wildcardKey)
    {
        $pattern = '/^' . str_replace('.', '\.', $wildcardKey);
        $pattern = str_replace('#', '.+', $pattern);
        $pattern = str_replace('*', '[^\.]+', $pattern);
        $pattern .= strpos($wildcardKey, '#') === strlen($wildcardKey) ? '/' : '$/';

        return $pattern;
    }
}
