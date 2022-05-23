<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

/**
 * Request path getters and setters for store code
 */
class StoreCodeInRequestPath implements StoreCodeInRequestPathInterface
{
    /**
     * Flag to check store code in request path
     *
     * @var bool
     */
    private bool $isStoreCodeInRequestPath = false;

    /**
     * Set flag, if store code is in request path ot not
     *
     * @param bool $status
     */
    public function setStoreCodeInRequestPath(bool $status): void
    {
        $this->isStoreCodeInRequestPath = $status;
    }

    /**
     * Get flag, if request path has store code or not
     *
     * @return bool
     */
    public function hasStoreCodeInRequestPath(): bool
    {
        return $this->isStoreCodeInRequestPath;
    }
}
