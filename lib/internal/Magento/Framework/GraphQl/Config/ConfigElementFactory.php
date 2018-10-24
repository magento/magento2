<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config;

/**
 * This factory allows instantiation of all supported GraphQL config element objects.
 *
 * It automatically detects the type of the object to be instantiated based on the provided config data.
 */
class ConfigElementFactory implements ConfigElementFactoryInterface
{
    /**
     * @var ConfigElementFactoryInterface[]
     */
    private $factoryMapByConfigElementType;

    /**
     * @param ConfigElementFactoryInterface[] $factoryMapByConfigElementType
     */
    public function __construct(
        array $factoryMapByConfigElementType
    ) {
        $this->factoryMapByConfigElementType = $factoryMapByConfigElementType;
    }

    /**
     * Instantiate config element based on its type specified in $data
     *
     * @param array $data
     * @return ConfigElementInterface
     */
    public function createFromConfigData(array $data): ConfigElementInterface
    {
        if (!isset($this->factoryMapByConfigElementType[$data['type']])) {
            throw new \LogicException(
                sprintf('Factory is not configured for config element of "%s" type', $data['type'])
            );
        }
        return $this->factoryMapByConfigElementType[$data['type']]->createFromConfigData($data);
    }
}
