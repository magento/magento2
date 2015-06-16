<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Api\ExtensionAttribute\JoinDataInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinDataInterfaceFactory;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Join processor allows to join extension attributes during collections loading.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JoinProcessor implements \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JoinDataInterfaceFactory
     */
    private $extensionAttributeJoinDataFactory;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Config $config
     * @param JoinDataInterfaceFactory $extensionAttributeJoinDataFactory
     * @param TypeProcessor $typeProcessor
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Config $config,
        JoinDataInterfaceFactory $extensionAttributeJoinDataFactory,
        TypeProcessor $typeProcessor,
        ExtensionAttributesFactory $extensionAttributesFactory
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->extensionAttributeJoinDataFactory = $extensionAttributeJoinDataFactory;
        $this->typeProcessor = $typeProcessor;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DbCollection $collection, $extensibleEntityClass = null)
    {
        $extensibleEntityClass = $extensibleEntityClass ?: $collection->getItemObjectClass();
        $joinDirectives = $this->getJoinDirectivesForType($extensibleEntityClass);
        foreach ($joinDirectives as $attributeCode => $directive) {
            /** @var JoinDataInterface $joinData */
            $joinData = $this->extensionAttributeJoinDataFactory->create();
            $joinData->setAttributeCode($attributeCode)
                ->setReferenceTable($directive[Converter::JOIN_REFERENCE_TABLE])
                ->setReferenceTableAlias($this->getReferenceTableAlias($attributeCode))
                ->setReferenceField($directive[Converter::JOIN_REFERENCE_FIELD])
                ->setJoinField($directive[Converter::JOIN_ON_FIELD]);
            $joinData->setSelectFields(
                $this->getSelectFieldsMap($attributeCode, $directive[Converter::JOIN_FIELDS])
            );
            $collection->joinExtensionAttribute($joinData, $this);
        }
    }

    /**
     * Generate a list of select fields with mapping of client facing attribute names to field names used in SQL select.
     *
     * @param string $attributeCode
     * @param array $selectFields
     * @return array
     */
    private function getSelectFieldsMap($attributeCode, $selectFields)
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
    private function getReferenceTableAlias($attributeCode)
    {
        return 'extension_attribute_' . $attributeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function extractExtensionAttributes($extensibleEntityClass, array $data)
    {
        if (!$this->isExtensibleAttributesImplemented($extensibleEntityClass)) {
            /* do nothing as there are no extension attributes */
            return $data;
        }

        $joinDirectives = $this->getJoinDirectivesForType($extensibleEntityClass);
        $extensionData = [];
        foreach ($joinDirectives as $attributeCode => $directive) {
            $this->populateAttributeCodeWithDirective(
                $attributeCode,
                $directive,
                $data,
                $extensionData,
                $extensibleEntityClass
            );
        }
        if (!empty($extensionData)) {
            $extensionAttributes = $this->extensionAttributesFactory->create($extensibleEntityClass, $extensionData);
            $data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] = $extensionAttributes;
        }
        return $data;
    }

    /**
     * Populate a specific attribute code with join directive instructions.
     *
     * @param string $attributeCode
     * @param array $directive
     * @param array &$data
     * @param array &$extensionData
     * @param string $extensibleEntityClass
     * @return void
     */
    private function populateAttributeCodeWithDirective(
        $attributeCode,
        $directive,
        &$data,
        &$extensionData,
        $extensibleEntityClass
    ) {
        $attributeType = $directive[Converter::DATA_TYPE];
        $selectFields = $this->getSelectFieldsMap($attributeCode, $directive[Converter::JOIN_FIELDS]);
        foreach ($selectFields as $selectField) {
            $internalAlias = $selectField[JoinDataInterface::SELECT_FIELD_INTERNAL_ALIAS];
            if (isset($data[$internalAlias])) {
                if ($this->typeProcessor->isArrayType($attributeType)) {
                    throw new \LogicException(
                        sprintf(
                            'Join directives cannot be processed for attribute (%s) of extensible entity (%s),'
                            . ' which has an Array type (%s).',
                            $attributeCode,
                            $this->extensionAttributesFactory->getExtensibleInterfaceName($extensibleEntityClass),
                            $attributeType
                        )
                    );
                } elseif ($this->typeProcessor->isTypeSimple($attributeType)) {
                    $extensionData['data'][$attributeCode] = $data[$internalAlias];
                    unset($data[$internalAlias]);
                    break;
                } else {
                    if (!isset($extensionData['data'][$attributeCode])) {
                        $extensionData['data'][$attributeCode] = $this->objectManager->create($attributeType);
                    }
                    $setterName = $selectField[JoinDataInterface::SELECT_FIELD_SETTER];
                    $extensionData['data'][$attributeCode]->$setterName($data[$internalAlias]);
                    unset($data[$internalAlias]);
                }
            }
        }
    }

    /**
     * Returns the internal join directive config for a given type.
     *
     * Array returned has all of the \Magento\Framework\Api\ExtensionAttribute\Config\Converter JOIN* fields set.
     *
     * @param string $extensibleEntityClass
     * @return array
     */
    private function getJoinDirectivesForType($extensibleEntityClass)
    {
        $extensibleInterfaceName = $this->extensionAttributesFactory
            ->getExtensibleInterfaceName($extensibleEntityClass);
        $extensibleInterfaceName = ltrim($extensibleInterfaceName, '\\');
        $config = $this->config->get();
        if (!isset($config[$extensibleInterfaceName])) {
            return [];
        }

        $typeAttributesConfig = $config[$extensibleInterfaceName];
        $joinDirectives = [];
        foreach ($typeAttributesConfig as $attributeCode => $attributeConfig) {
            if (isset($attributeConfig[Converter::JOIN_DIRECTIVE])) {
                $joinDirectives[$attributeCode] = $attributeConfig[Converter::JOIN_DIRECTIVE];
                $joinDirectives[$attributeCode][Converter::DATA_TYPE] = $attributeConfig[Converter::DATA_TYPE];
            }
        }

        return $joinDirectives;
    }

    /**
     * Determine if the type is an actual extensible data interface.
     *
     * @param string $typeName
     * @return bool
     */
    private function isExtensibleAttributesImplemented($typeName)
    {
        try {
            $this->extensionAttributesFactory->getExtensibleInterfaceName($typeName);
            return true;
        } catch (\LogicException $e) {
            return false;
        }
    }
}
