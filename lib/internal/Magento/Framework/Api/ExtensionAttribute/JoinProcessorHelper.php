<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter as Converter;
use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Join processor helper class
 */
class JoinProcessorHelper
{
    /** @var Config */
    private $config;

    /** @var JoinDataInterfaceFactory */
    private $joinDataIntfFactory;

    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param JoinDataInterfaceFactory $joinDataIntfFactory
     */
    public function __construct(
        Config $config,
        JoinDataInterfaceFactory $joinDataIntfFactory
    ) {
        $this->config = $config;
        $this->joinDataIntfFactory = $joinDataIntfFactory;
    }

    /**
     * Generate a list of select fields with mapping of client facing attribute names to field names used in SQL select.
     *
     * @param string $attributeCode
     * @param array $selectFields
     * @return array
     */
    public function getSelectFieldsMap($attributeCode, $selectFields)
    {
        $referenceTableAlias = $this->getReferenceTableAlias($attributeCode);
        $useFieldInAlias = (count($selectFields) > 1);
        $selectFieldsAliases = [];

        foreach ($selectFields as $selectField) {
            $internalFieldName = $selectField[Converter::JOIN_FIELD_COLUMN]
                ? $selectField[Converter::JOIN_FIELD_COLUMN]
                : $selectField[Converter::JOIN_FIELD];
            $setterName = 'set'
                . ucfirst(SimpleDataObjectConverter::snakeCaseToCamelCase($selectField[Converter::JOIN_FIELD]));
            $selectFieldsAliases[] = [
                JoinDataInterface::SELECT_FIELD_EXTERNAL_ALIAS => $attributeCode
                    . ($useFieldInAlias ? '.' . $selectField[Converter::JOIN_FIELD] : ''),
                JoinDataInterface::SELECT_FIELD_INTERNAL_ALIAS => $referenceTableAlias . '_' . $internalFieldName,
                JoinDataInterface::SELECT_FIELD_WITH_DB_PREFIX => $referenceTableAlias . '.' . $internalFieldName,
                JoinDataInterface::SELECT_FIELD_SETTER => $setterName
            ];
        }
        return $selectFieldsAliases;
    }

    /**
     * Generate reference table alias.
     *
     * @param string $attributeCode
     * @return string
     */
    public function getReferenceTableAlias($attributeCode)
    {
        return 'extension_attribute_' . $attributeCode;
    }

    /**
     * config getter
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * joinDataIntFactory getter
     *
     * @return JoinDataInterfaceFactory
     */
    public function getJoinDataInterfaceFactory()
    {
        return $this->joinDataIntfFactory;
    }
}
