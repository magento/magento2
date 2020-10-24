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
     * @var string
     */
    private $signature;
    /**
     * @var string
     */
    private $data;
    /**
     * @var int
     */
    private $timestamp;

    /**
     * @param string $signature
     * @param string $data
     * @param int $timestamp
     */
    public function __construct(
        string $signature,
        string $data,
        int $timestamp
    ) {
        $this->signature = $signature;
        $this->data = $data;
        $this->timestamp = $timestamp;
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
