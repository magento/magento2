<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\GraphQl\Config\ConfigInterface;

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
     * @param ConfigInterface $typeConfig
     */
    public function __construct(ConfigInterface $typeConfig)
    {
        $this->typeConfig = $typeConfig;
    }

    /**
     * Convert an actual value from a query to an enum value declared as an item in the graphql.xml schema
     *
     * @param int|string|bool|float|null $queryValue
     * @param string $enumType
     * @return int|string|bool|float|null
     */
    public function getEnumValue($queryValue, $enumType)
    {
        $priceViewEnum = $this->typeConfig->getTypeStructure($enumType);
        foreach ($priceViewEnum->getValues() as $enumItem) {
            if ($enumItem->getName() == $queryValue) {
                return $enumItem->getValue();
            }
        }
    }
}
