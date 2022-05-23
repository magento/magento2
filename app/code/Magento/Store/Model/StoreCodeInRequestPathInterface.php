<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

/**
 * Store code in http request path interface
 */
interface StoreCodeInRequestPathInterface
{
    /**
     * Set flag, if store code is in request path ot not
     *
     * @param bool $status
     */
    public function setStoreCodeInRequestPath(bool $status): void;

    /**
     * Get flag, if request path has store code or not
     *
     * @return bool
     */
    public function hasStoreCodeInRequestPath() : bool;
}
