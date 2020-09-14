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
     * @var int|null
     */
    private $customerId;

    /**
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     * @param int|null $customerId
     */
    public function __construct(
        StoreInterface $fromStore,
        StoreInterface $targetStore,
        string $redirectUrl,
        ?int $customerId = null
    ) {
        $this->fromStore = $fromStore;
        $this->targetStore = $targetStore;
        $this->redirectUrl = $redirectUrl;
        $this->customerId = $customerId;
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

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }
}
