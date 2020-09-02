<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\GraphQl\Config\Element\Enum;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Schema\Type\Enum\DataMapperInterface;

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
     * EnumLookup constructor.
     *
     * @param ConfigInterface $typeConfig
     * @param DataMapperInterface $enumDataMapper
     */
    public function __construct(ConfigInterface $typeConfig, DataMapperInterface $enumDataMapper)
    {
        $this->typeConfig = $typeConfig;
        $this->enumDataMapper = $enumDataMapper;
    }

    /**
     * Convert a field value from a db query to an enum value declared as an item in the graphql schema
     *
     * @param string $enumName
     * @param string $fieldValue
     * @return string
     */
    public function getEnumValueFromField(string $enumName, string $fieldValue) : string
    {
        /** @var Enum $enumObject */
        $enumObject = $this->typeConfig->getConfigElement($enumName);
        $mappedValues = $this->enumDataMapper->getMappedEnums($enumName);

        foreach ($enumObject->getValues() as $enumItem) {
            if (isset($mappedValues[$enumItem->getName()]) && $mappedValues[$enumItem->getName()] === $fieldValue) {
                return $enumItem->getValue();
            }
        }

        return '';
    }
}
