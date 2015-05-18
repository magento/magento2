<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Config\Reader;
use Magento\Framework\Api\Config\Converter;
use Magento\Framework\Data\Collection\Db as DbCollection;

/**
 * JoinProcessor configures a ExtensibleDateInterface type's collection to retrieve data in related tables.
 */
class JoinProcessor
{
    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @param Reader $configReader
     */
    public function __construct(
        Reader $configReader
    ) {
        $this->configReader = $configReader;
    }

    /**
     * Processes join instructions to add to the collection for a data interface.
     *
     * @param DbCollection $collection
     * @param string $dataInterfaceName
     * @return void
     */
    public function process(DbCollection $collection, $dataInterfaceName)
    {
        $joinDirectives = $this->getJoinDirectivesForType($dataInterfaceName);
        foreach ($joinDirectives as $attributeCode => $directive) {
            $selectFields = explode(',', $directive[Converter::JOIN_SELECT_FIELDS]);
            foreach ($selectFields as $selectField) {
                $join = [
                    'alias' => 'extension_attribute_' . $attributeCode,
                    'table' => $directive[Converter::JOIN_REFERENCE_TABLE],
                    'field' => trim($selectField),
                    'join_field' => $directive[Converter::JOIN_JOIN_ON_FIELD],
                ];
                $collection->joinField($join);
            }
        }
    }

    /**
     * @param string $typeName
     * @return array
     */
    private function getJoinDirectivesForType($typeName)
    {
        $config = $this->configReader->read();
        if (!isset($config[$typeName])) {
            return [];
        }

        $typeAttributesConfig = $config[$typeName];
        $joinDirectives = [];
        foreach ($typeAttributesConfig as $attributeCode => $attributeConfig) {
            if (isset($attributeConfig[Converter::JOIN_DIRECTIVE])) {
                $joinDirectives[$attributeCode] = $attributeConfig[Converter::JOIN_DIRECTIVE];
            }
        }

        return $joinDirectives;
    }
}
