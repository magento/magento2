<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\Cache\Backend\Redis;
use Magento\Framework\Cache\FrontendInterface;

/**
 * Logging requests to cache
 */
class CacheRequestLogger implements RequestLoggerInterface
{
    /**
     * @var FrontendInterface
     */
    private FrontendInterface $cache;

    /**
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function incrAndGetFor(ContextInterface $context, int $timeSlot, int $discardAfter): int
    {
        $id = $this->generateId($context, $timeSlot);

        if ($this->cache->getBackend() instanceof Redis) {
            //Atomic way with redis
            /** @var Redis $cache */
            $cache = $this->cache->getBackend();

            return $cache->updateByAndGet($id, 1, time() + $discardAfter);
        }

        //Non-atomic way
        $n = (int) ($this->cache->load($id) ?? 0);
        $this->cache->save((string) ++$n, $id, [], $discardAfter);

        return $n;
    }

    /**
     * @inheritDoc
     */
    public function getFor(ContextInterface $context, int $timeSlot): ?int
    {
        $value = $this->cache->load($this->generateId($context, $timeSlot));

        if (empty($value)) {
            return null;
        }
        return (int) $value;
    }

    /**
     * Generate cache ID based on context.
     *
     * @param ContextInterface $context
     * @param int $timeSlot
     * @return string
     */
    private function generateId(ContextInterface $context, int $timeSlot): string
    {
        return 'reqlog' . $context->getTypeId() . $context->getIdentityType() . $context->getIdentity() . $timeSlot;
    }
}
