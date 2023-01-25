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
     * @var StoreInterface
     */
    private $fromStore;
    /**
     * @var StoreInterface
     */
    private $targetStore;
    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     */
    public function __construct(
        StoreInterface $fromStore,
        StoreInterface $targetStore,
        string $redirectUrl
    ) {
        $this->fromStore = $fromStore;
        $this->targetStore = $targetStore;
        $this->redirectUrl = $redirectUrl;
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
