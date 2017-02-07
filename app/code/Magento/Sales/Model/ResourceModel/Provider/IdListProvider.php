<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Implements IdListProviderInterface as composite
 */
class IdListProvider implements IdListProviderInterface
{
    /**
     * @var IdListProviderInterface[]
     */
    private $providers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $providers
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $providers = []
    ) {
        $this->providers = $tmapFactory->create(
            [
                'array' => $providers,
                'type' => IdListProviderInterface::class
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function get($mainTableName, $gridTableName)
    {
        $result = [];
        foreach ($this->providers as $provider) {
            $result = array_merge($result, $provider->get($mainTableName, $gridTableName));
        }

        return $result;
    }
}
