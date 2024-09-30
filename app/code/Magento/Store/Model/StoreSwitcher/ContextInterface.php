<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Store switcher context interface
 */
interface ContextInterface
{
    /**
     * Store to switch from
     *
     * @return StoreInterface
     */
    public function getFromStore(): StoreInterface;

    /**
     * Store to switch to
     *
     * @return StoreInterface
     */
    public function getTargetStore(): StoreInterface;

    /**
     * The URL to redirect after switching store
     *
     * @return string
     */
    public function getRedirectUrl(): string;
}
