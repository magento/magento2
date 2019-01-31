<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;

/**
 * Class ChangeStoreCode
 *
 * Changes store code in redirect url from current store code to target store code
 *
 * @package Magento\Store\Model\StoreSwitcher
 * @since   2.3.0
 */
class ChangeStoreCode implements StoreSwitcherInterface
{
    /**
     * @inheritdoc
     * @since 2.3.0
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        if ($fromStore->isUseStoreInUrl()) {
            if (\strpos($redirectUrl, $fromStore->getBaseUrl()) !== false) {
                $redirectUrl = \str_replace($fromStore->getBaseUrl(), $targetStore->getBaseUrl(), $redirectUrl);
            } else {
                $redirectUrl = $targetStore->getBaseUrl();
            }
        }

        return $redirectUrl;
    }
}
