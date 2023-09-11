<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException;

/**
 * Handles store switching procedure and detects url for final redirect after store switching.
 *
 * @api
 */
interface StoreSwitcherInterface
{
    /**
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string url to be redirected after switching
     * @throws CannotSwitchStoreException
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string;
}
