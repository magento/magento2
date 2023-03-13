<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data
 */
class RedirectData implements RedirectDataInterface
{
    /**
     * @param string $signature
     * @param string $data
     * @param int $timestamp
     */
    public function __construct(
        private readonly string $signature,
        private readonly string $data,
        private readonly int $timestamp
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @inheritDoc
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
