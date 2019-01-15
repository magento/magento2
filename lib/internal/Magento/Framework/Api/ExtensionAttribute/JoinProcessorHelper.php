<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinDataInterfaceFactory
     */
    private $joinDataInterfaceFactory;

    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param JoinDataInterfaceFactory $joinDataInterfaceFactory
     */
    public function __construct(
        Config $config,
        JoinDataInterfaceFactory $joinDataInterfaceFactory
    ) {
        $this->config = $config;
        $this->joinDataInterfaceFactory = $joinDataInterfaceFactory;
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
     * Returns config data values
     *
     * @return array|mixed|null
     */
    public function getConfigData()
    {
        return $this->config->get();
    }

    /**
     * JoinDataInterface getter
     *
     * @return JoinDataInterface
     */
    public function getJoinDataInterface()
    {
        return $this->joinDataInterfaceFactory->create();
    }
}
