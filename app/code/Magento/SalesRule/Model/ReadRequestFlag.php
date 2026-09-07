<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

/**
 * Tracks whether its a read request
 *
 * This class is used to optimize performance by only loading salesrule product attributes
 * when they're actually needed during totals collection, rather than on every quote load.
 */
class ReadRequestFlag
{
    /**
     * @var bool
     */
    private $isReadRequest= false;

    /**
     * Set Request state
     *
     * @param bool $state
     * @return void
     */
    public function setIsReadRequest(bool $state): void
    {
        $this->isReadRequest = $state;
    }

    /**
     * Check if request is a read or write
     *
     * @return bool
     */
    public function isReadRequest(): bool
    {
        return $this->isReadRequest;
    }

    /**
     * Resets the state of the object by setting the isReadRequest property to false.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->isReadRequest = false;
    }
}
