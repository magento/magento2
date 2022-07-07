<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\GraphQl\Config\ConfigElementFactoryInterface;
use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for config elements of 'union' type.
 */
class UnionFactory implements ConfigElementFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Instantiate an object representing 'union' GraphQL config element.
     *
     * @param array $data
     * @return ConfigElementInterface
     */
    public function createFromConfigData(array $data): ConfigElementInterface
    {
        return $this->create($data, $data['types'] ?? []);
    }

    /**
     * Create union object based off array of configured GraphQL.
     *
     * Union data must contain name, type resolver, and possible concrete types definitions
     * The type resolver should point to an implementation of the TypeResolverInterface
     * that decides what concrete GraphQL type to output. Description is the only optional field.
     *
     * @param array $unionData
     * @param array $types
     * @return UnionType
     */
    public function create(
        array $unionData,
        array $types
    ) : UnionType {
        return $this->objectManager->create(
            UnionType::class,
            [
                'name' => $unionData['name'],
                'typeResolver' => $unionData['typeResolver'],
                'types' => $types,
                'description' => isset($unionData['description']) ? $unionData['description'] : ''
            ]
        );
    }
}
