<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Store switcher context
 */
class Context implements ContextInterface
{
    /**
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     */
    public function __construct(
        private readonly StoreInterface $fromStore,
        private readonly StoreInterface $targetStore,
        private readonly string $redirectUrl
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getFromStore(): StoreInterface
    {
        return $this->fromStore;
    }

    /**
     * @inheritDoc
     */
    public function getTargetStore(): StoreInterface
    {
        return $this->targetStore;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
