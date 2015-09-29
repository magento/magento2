<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
 */
class JoinProcessor implements \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /** @var TypeProcessor */
    private $typeProcessor;

    /** @var ExtensionAttributesFactory */
    private $extAttribFactory;

    /** @var JoinProcessorHelper */
    private $joinProcessorHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ExtensionAttributesFactory $extAttribFactory
     * @param TypeProcessor $typeProcessor
     * @param JoinProcessorHelper $joinProcessorHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        TypeProcessor $typeProcessor,
        ExtensionAttributesFactory $extAttribFactory,
        JoinProcessorHelper $joinProcessorHelper
    ) {
        $this->objectManager = $objectManager;
        $this->typeProcessor = $typeProcessor;
        $this->extAttribFactory = $extAttribFactory;
        $this->joinProcessorHelper = $joinProcessorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DbCollection $collection, $extensibleClass = null)
    {
        $extensibleClass = $extensibleClass ?: $collection->getItemObjectClass();
        $joinDirectives = $this->getJoinDirectivesForType($extensibleClass);

        foreach ($joinDirectives as $attributeCode => $directive) {
            /** @var JoinDataInterface $joinData */
            $joinData = $this->joinProcessorHelper->getJoinDataInterfaceFactory()->create();
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
     */
    private function getReferenceTableAlias($attributeCode)
    {
        return 'extension_attribute_' . $attributeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function extractExtensionAttributes($extensibleClass, array $data)
    {
        if (!$this->isExtensibleAttributesImplemented($extensibleClass)) {
            /* do nothing as there are no extension attributes */
            return $data;
        }

        $joinDirectives = $this->getJoinDirectivesForType($extensibleClass);
        $extensionData = [];
        foreach ($joinDirectives as $attributeCode => $directive) {
            $this->populateAttributeCodeWithDirective(
                $attributeCode,
                $directive,
                $data,
                $extensionData,
                $extensibleClass
            );
        }
        if (!empty($extensionData)) {
            $extensionAttributes = $this->extAttribFactory->create($extensibleClass, $extensionData);
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
     * @param string $extensibleClass
     * @return void
     */
    private function populateAttributeCodeWithDirective(
        $attributeCode,
        $directive,
        &$data,
        &$extensionData,
        $extensibleClass
    ) {
        /** @var JoinDataInterface $joinData */
        $joinData = $this->joinProcessorHelper->getJoinDataInterfaceFactory()->create();
        $attributeType = $directive[Converter::DATA_TYPE];
        $selectFields = $this->joinProcessorHelper
            ->getSelectFieldsMap($attributeCode, $directive[Converter::JOIN_FIELDS]);

        foreach ($selectFields as $selectField) {
            $internalAlias = $selectField[$joinData::SELECT_FIELD_INTERNAL_ALIAS];
            if (isset($data[$internalAlias])) {
                if ($this->typeProcessor->isArrayType($attributeType)) {
                    throw new \LogicException(
                        sprintf(
                            'Join directives cannot be processed for attribute (%s) of extensible entity (%s),'
                            . ' which has an Array type (%s).',
                            $attributeCode,
                            $this->extAttribFactory->getExtensibleInterfaceName($extensibleClass),
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
                    $setterName = $selectField[$joinData::SELECT_FIELD_SETTER];
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
     * @param string $extensibleClass
     * @return array
     */
    private function getJoinDirectivesForType($extensibleClass)
    {
        $extensibleIntf = $this->extAttribFactory->getExtensibleInterfaceName($extensibleClass);
        $extensibleIntf = ltrim($extensibleIntf, '\\');
        $config = $this->joinProcessorHelper->getConfig()->get();
        if (!isset($config[$extensibleIntf])) {
            return [];
        }

        $typeAttributesConfig = $config[$extensibleIntf];
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
            $this->extAttribFactory->getExtensibleInterfaceName($typeName);
            return true;
        } catch (\LogicException $e) {
            return false;
        }
    }
}
