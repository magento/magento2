<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Distance provider pool for distance based source selection
 *
 * @api
 */
class DistanceProviderPool
{
    /**
     * @var array
     */
    private $providers;

    /**
     * DistanceProviderPool constructor.
     *
     * @param array $providers
     */
    public function __construct(
        array $providers
    ) {
        $this->providers = $providers;

        foreach ($this->providers as $providerCode => $provider) {
            if (!($provider instanceof DistanceProviderInterface)) {
                throw new \InvalidArgumentException(
                    'Distance provider ' . $providerCode . ' must implement DistanceProviderInterface'
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(): array
    {
        return $this->providers;
    }

    /**
     * @inheritdoc
     */
    public function getProvider(string $code): DistanceProviderInterface
    {
        if (!isset($this->providers[$code])) {
            throw new NoSuchEntityException(__('Unknown distance provider %1', $code));
        }

        return $this->providers[$code];
    }
}
