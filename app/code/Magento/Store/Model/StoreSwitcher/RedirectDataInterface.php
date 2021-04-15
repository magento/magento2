<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data interface
 *
 * @api
 */
interface RedirectDataInterface
{
    /**
     * Redirect data signature
     *
     * @return string
     */
    public function getSignature(): string;

    /**
     * Data to redirect from store to store
     *
     * @return string
     */
    public function getData(): string;

    /**
     * Expire date of the redirect data
     *
     * @return int
     */
    public function getTimestamp(): int;
}
