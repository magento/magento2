<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter as Converter;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Join processor allows to join extension attributes during collections loading.
 * @since 2.0.0
 */
class JoinProcessor implements \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     * @since 2.0.0
     */
    private $typeProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     * @since 2.0.0
     */
    private $extensionAttributesFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorHelper
     * @since 2.0.0
     */
    private $joinProcessorHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param TypeProcessor $typeProcessor
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param JoinProcessorHelper $joinProcessorHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        TypeProcessor $typeProcessor,
        ExtensionAttributesFactory $extensionAttributesFactory,
        JoinProcessorHelper $joinProcessorHelper
    ) {
        $this->objectManager = $objectManager;
        $this->typeProcessor = $typeProcessor;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->joinProcessorHelper = $joinProcessorHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function process(DbCollection $collection, $extensibleEntityClass = null)
    {
        $extensibleEntityClass = $extensibleEntityClass ?: $collection->getItemObjectClass();
        $joinDirectives = $this->getJoinDirectivesForType($extensibleEntityClass);

        foreach ($joinDirectives as $attributeCode => $directive) {
            /** @var JoinDataInterface $joinData */
            $joinData = $this->joinProcessorHelper->getJoinDataInterface();
            $joinData->setAttributeCode($attributeCode)
                ->setReferenceTable($directive[Converter::JOIN_REFERENCE_TABLE])
                ->setReferenceTableAlias($this->getReferenceTableAlias($attributeCode))
                ->setReferenceField($directive[Converter::JOIN_REFERENCE_FIELD])
                ->setJoinField($directive[Converter::JOIN_ON_FIELD]);
            $joinData->setSelectFields(
                $this->joinProcessorHelper->getSelectFieldsMap($attributeCode, $directive[Converter::JOIN_FIELDS])
            );
            $collection->joinExtensionAttribute($joinData, $this);
        }
    }

    /**
     * Generate reference table alias.
     *
     * @param string $attributeCode
     * @return string
     * @since 2.0.0
     */
    private function getReferenceTableAlias($attributeCode)
    {
        return 'extension_attribute_' . $attributeCode;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function populateAttributeCodeWithDirective(
        $attributeCode,
        $directive,
        &$data,
        &$extensionData,
        $extensibleEntityClass
    ) {
        $attributeType = $directive[Converter::DATA_TYPE];
        $selectFields = $this->joinProcessorHelper
            ->getSelectFieldsMap($attributeCode, $directive[Converter::JOIN_FIELDS]);

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
     * @since 2.0.0
     */
    private function getJoinDirectivesForType($extensibleEntityClass)
    {
        $extensibleInterfaceName = $this->extensionAttributesFactory
            ->getExtensibleInterfaceName($extensibleEntityClass);
        $extensibleInterfaceName = ltrim($extensibleInterfaceName, '\\');
        $config = $this->joinProcessorHelper->getConfigData();
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
     * @since 2.0.0
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
