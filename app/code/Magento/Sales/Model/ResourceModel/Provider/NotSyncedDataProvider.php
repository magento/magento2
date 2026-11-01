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
class NotSyncedDataProvider implements NotSyncedDataProviderInterface, NotSyncedDataProviderWithCutoffInterface
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
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->sub(new \DateInterval('PT1S'))
            ->format('Y-m-d H:i:s');
        return $this->getIdsWithCutoff($mainTableName, $gridTableName, $cutoff);
    }

    /**
     * @inheritDoc
     */
    public function getIdsWithCutoff($mainTableName, $gridTableName, $cutoff)
    {
        $result = [];
        foreach ($this->providers as $provider) {
            if ($provider instanceof NotSyncedDataProviderWithCutoffInterface) {
                $result[] = $provider->getIdsWithCutoff($mainTableName, $gridTableName, $cutoff);
            } else {
                $result[] = $provider->getIds($mainTableName, $gridTableName);
            }
        }

        return array_unique(array_merge([], ...$result));
    }
}
