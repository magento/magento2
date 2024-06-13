<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Implements NotSyncedDataProviderInterface as composite
 */
class NotSyncedDataProvider implements NotSyncedDataProviderInterface
{
    /**
     * @var NotSyncedDataProviderInterface[]
     */
    private $providers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $providers
     */
    public function __construct(TMapFactory $tmapFactory, array $providers = [])
    {
        $this->providers = $tmapFactory->create(
            [
                'array' => $providers,
                'type' => NotSyncedDataProviderInterface::class
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $result = [];
        foreach ($this->providers as $provider) {
            $result[] = $provider->getIds($mainTableName, $gridTableName);
        }

        return array_unique(array_merge([], ...$result));
    }
}
