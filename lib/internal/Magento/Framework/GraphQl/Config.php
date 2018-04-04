<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\GraphQl\Config\ConfigElementFactoryInterface;
use Magento\Framework\GraphQl\Config\ConfigElementInterface;

/**
 * Provides access to typing information for a configured GraphQL schema.
 */
class Config implements ConfigInterface
{
    /**
     * @var DataInterface
     */
    private $configData;

    /**
     * @var ConfigElementFactoryInterface
     */
    private $configElementFactory;

    /**
     * @param DataInterface $data
     * @param ConfigElementFactoryInterface $configElementFactory
     */
    public function __construct(
        DataInterface $data,
        ConfigElementFactoryInterface $configElementFactory
    ) {
        $this->configData = $data;
        $this->configElementFactory = $configElementFactory;
    }

    /**
     * Get a data object with data pertaining to a GraphQL type's structural makeup.
     *
     * @param string $configElementName
     * @return ConfigElementInterface
     * @throws \LogicException
     */
    public function getConfigElement(string $configElementName) : ConfigElementInterface
    {
        $data = $this->configData->get($configElementName);
        if (!isset($data['type'])) {
            throw new \LogicException(
                sprintf('Config element "%s" is not declared in GraphQL schema', $configElementName)
            );
        }
        return $this->configElementFactory->createFromConfigData($data);
    }

    /**
     * Return all type names declared in a GraphQL schema's configuration.
     *
     * @return string[]
     */
    public function getDeclaredTypeNames() : array
    {
        $types = [];
        foreach ($this->configData->get(null) as $item) {
            if (isset($item['type']) && $item['type'] == 'graphql_type') {
                $types[] = $item['name'];
            }
        }
        return $types;
    }
}
