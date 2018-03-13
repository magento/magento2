<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\GraphQl\Config\ConfigInterface;
use Magento\Framework\GraphQl\Type\Enum\DataMapperInterface;
use Magento\Framework\GraphQl\Config\Data\Enum;
use Magento\Framework\Phrase;

/**
 * Processor that looks up definition data of an enum to lookup and convert data as it's specified in the schema.
 */
class EnumLookup
{
    /**
     * @var ConfigInterface
     */
    private $typeConfig;

    /**
     * @var DataMapperInterface
     */
    private $enumDataMapper;

    /**
     * @param ConfigInterface $typeConfig
     * @param DataMapperInterface $enumDataMapper
     */
    public function __construct(ConfigInterface $typeConfig, DataMapperInterface $enumDataMapper)
    {
        $this->typeConfig = $typeConfig;
        $this->enumDataMapper = $enumDataMapper;
    }

    /**
     * Convert a field value from a db query to an enum value declared as an item in the graphql.xml schema
     *
     * @param string $enumName
     * @param int|string|bool|float|null $fieldValue
     * @return int|string|bool|float|null
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getEnumValueFromField(string $enumName, $fieldValue)
    {
        $priceViewEnum = $this->typeConfig->getTypeStructure($enumName);
        if ($priceViewEnum instanceof Enum) {
            foreach ($priceViewEnum->getValues() as $enumItem) {
                $mappedValues = $this->enumDataMapper->getMappedEnums($enumName);
                if (isset($mappedValues[$enumItem->getName()]) && $mappedValues[$enumItem->getName()] == $fieldValue) {
                    return $enumItem->getValue();
                }
            }
        } else {
            throw new \Magento\Framework\Exception\RuntimeException(
                new Phrase('Enum type "%1" not defined', [$enumName])
            );
        }
    }
}
