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
 */
class StoreSwitcher implements StoreSwitcherInterface
{
    /**
     * @var StoreSwitcherInterface[]
     */
    private $storeSwitchers;

    /**
     * @param StoreSwitcherInterface[] $storeSwitchers
     * @throws \Exception
     */
    public function __construct(array $storeSwitchers)
    {
        foreach ($storeSwitchers as $switcherName => $switcherInstance) {
            if (!$switcherInstance instanceof StoreSwitcherInterface) {
                throw new \InvalidArgumentException(
                    "Store switcher '{$switcherName}' is expected to implement interface "
                    . StoreSwitcherInterface::class
                );
            }
        }
        $this->storeSwitchers = $storeSwitchers;
    }

    /**
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string url to be redirected after switching
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws CannotSwitchStoreException
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;

        foreach ($this->storeSwitchers as $storeSwitcher) {
            $targetUrl = $storeSwitcher->switch($fromStore, $targetStore, $targetUrl);
        }

        return $targetUrl;
    }
}
