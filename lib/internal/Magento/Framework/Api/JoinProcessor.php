<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Config\Reader;
use Magento\Framework\Api\Config\Converter;
use Magento\Framework\Data\Collection\Db as DbCollection;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinData;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinDataFactory;

/**
 * JoinProcessor configures an ExtensibleDateInterface type's collection to retrieve data in related tables.
 */
class JoinProcessor
{
    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @var ExtensionAttributeJoinDataFactory
     */
    private $extensionAttributeJoinDataFactory;

    /**
     * Initialize dependencies.
     *
     * @param Reader $configReader
     * @param ExtensionAttributeJoinDataFactory $extensionAttributeJoinDataFactory
     */
    public function __construct(
        Reader $configReader,
        ExtensionAttributeJoinDataFactory $extensionAttributeJoinDataFactory
    ) {
        $this->configReader = $configReader;
        $this->extensionAttributeJoinDataFactory = $extensionAttributeJoinDataFactory;
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
                /** @var ExtensionAttributeJoinData $joinData */
                $joinData = $this->extensionAttributeJoinDataFactory->create();
                $joinData->setReferenceTable($directive[Converter::JOIN_REFERENCE_TABLE])
                    ->setReferenceTableAlias('extension_attribute_' . $attributeCode)
                    ->setReferenceField($directive[Converter::JOIN_REFERENCE_FIELD])
                    ->setJoinField($directive[Converter::JOIN_JOIN_ON_FIELD])
                    ->setSelectField(trim($selectField));
                $collection->joinExtensionAttribute($joinData);
            }
        }
    }

    /**
     * Returns the internal join directive config for a given type.
     *
     * Array returned has all of the \Magento\Framework\Api\Config\Converter JOIN* fields set.
     *
     * @param string $typeName
     * @return array
     */
    private function getJoinDirectivesForType($typeName)
    {
        $typeName = ltrim($typeName, '\\');
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
