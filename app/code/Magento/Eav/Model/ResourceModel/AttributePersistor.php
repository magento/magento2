<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class AttributePersistor
 */
class AttributePersistor
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var array
     */
    private $insert = [];

    /**
     * @var array
     */
    private $update = [];

    /**
     * @var array
     */
    private $delete = [];

    /**
     * @param FormatInterface $localeFormat
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        FormatInterface $localeFormat,
        AttributeRepositoryInterface $attributeRepository,
        MetadataPool $metadataPool
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->localeFormat = $localeFormat;
    }

    /**
     * @param string $entityType
     * @param int $link
     * @param string $attributeCode
     * @return void
     */
    public function registerDelete($entityType, $link, $attributeCode)
    {
        $this->delete[$entityType][$link][$attributeCode] = null;
    }

    /**
     * @param string $entityType
     * @param int $link
     * @param string $attributeCode
     * @param mixed $value
     * @return void
     */
    public function registerUpdate($entityType, $link, $attributeCode, $value)
    {
        $this->update[$entityType][$link][$attributeCode] = $value;
    }

    /**
     * @param string $entityType
     * @param int $link
     * @param string $attributeCode
     * @param mixed $value
     * @return void
     */
    public function registerInsert($entityType, $link, $attributeCode, $value)
    {
        $this->insert[$entityType][$link][$attributeCode] = $value;
    }

    /**
     * @param string $entityType
     * @param \Magento\Framework\Model\Entity\ScopeInterface[] $context
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processDeletes($entityType, $context)
    {
        if (!isset($this->delete[$entityType]) || !is_array($this->delete[$entityType])) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        $linkField = $metadata->getLinkField();
        foreach ($this->delete[$entityType] as $link => $data) {
            $attributeCodes = array_keys($data);
            foreach ($attributeCodes as $attributeCode) {
                /** @var AbstractAttribute $attribute */
                $attribute = $this->attributeRepository->get($metadata->getEavEntityType(), $attributeCode);
                $conditions = [
                    $linkField . ' = ?' => $link,
                    'attribute_id = ?' => $attribute->getAttributeId()
                ];
                foreach ($context as $scope) {
                    $conditions[$metadata->getEntityConnection()->quoteIdentifier($scope->getIdentifier()) . ' = ?']
                        = $this->getScopeValue($scope, $attribute);
                }
                $metadata->getEntityConnection()->delete(
                    $attribute->getBackend()->getTable(),
                    $conditions
                );
            }
        }
    }

    /**
     * @param string $entityType
     * @param \Magento\Framework\Model\Entity\ScopeInterface[] $context
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processInserts($entityType, $context)
    {
        if (!isset($this->insert[$entityType]) || !is_array($this->insert[$entityType])) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        $linkField = $metadata->getLinkField();
        foreach ($this->insert[$entityType] as $link => $data) {
            foreach ($data as $attributeCode => $attributeValue) {
                /** @var AbstractAttribute $attribute */
                $attribute = $this->attributeRepository->get(
                    $metadata->getEavEntityType(),
                    $attributeCode
                );
                $data = [
                    $linkField => $link,
                    'attribute_id' => $attribute->getAttributeId(),
                    'value' => $this->prepareValue($entityType, $attributeValue, $attribute)
                ];
                foreach ($context as $scope) {
                    $data[$scope->getIdentifier()] = $this->getScopeValue($scope, $attribute);
                }
                $metadata->getEntityConnection()->insertOnDuplicate($attribute->getBackend()->getTable(), $data);
            }
        }
    }

    /**
     * @param string $entityType
     * @param \Magento\Framework\Model\Entity\ScopeInterface[] $context
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processUpdates($entityType, $context)
    {
        if (!isset($this->update[$entityType]) || !is_array($this->update[$entityType])) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        $linkField = $metadata->getLinkField();
        foreach ($this->update[$entityType] as $link => $data) {
            foreach ($data as $attributeCode => $attributeValue) {
                /** @var AbstractAttribute $attribute */
                $attribute = $this->attributeRepository->get(
                    $metadata->getEavEntityType(),
                    $attributeCode
                );
                $conditions = [
                    $linkField . ' = ?' => $link,
                    'attribute_id = ?' => $attribute->getAttributeId(),
                ];
                foreach ($context as $scope) {
                    $conditions[$metadata->getEntityConnection()->quoteIdentifier($scope->getIdentifier()) . ' = ?']
                        = $this->getScopeValue($scope, $attribute);
                }
                $metadata->getEntityConnection()->update(
                    $attribute->getBackend()->getTable(),
                    [
                        'value' => $this->prepareValue($entityType, $attributeValue, $attribute)
                    ],
                    $conditions
                );
            }
        }
    }

    /**
     * Flush attributes to storage
     *
     * @param string $entityType
     * @param string $context
     * @return void
     */
    public function flush($entityType, $context)
    {
        $this->processDeletes($entityType, $context);
        $this->processInserts($entityType, $context);
        $this->processUpdates($entityType, $context);
        unset($this->delete, $this->insert, $this->update);
    }

    /**
     * @param string $entityType
     * @param string $value
     * @param AbstractAttribute $attribute
     * @return mixed
     * @throws \Exception
     */
    protected function prepareValue($entityType, $value, AbstractAttribute $attribute)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $type = $attribute->getBackendType();
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        } elseif ($type == 'decimal') {
            $value = $this->localeFormat->getNumber($value);
        } elseif ($type == 'varchar' && is_array($value)) {
            $value = implode(',', $value);
        }
        $describe = $metadata->getEntityConnection()->describeTable($attribute->getBackendTable());
        return $metadata->getEntityConnection()->prepareColumnValue($describe['value'], $value);
    }

    /**
     * @param ScopeInterface $scope
     * @param AbstractAttribute $attribute
     * @param bool $useDefault
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getScopeValue(ScopeInterface $scope, AbstractAttribute $attribute, $useDefault = false)
    {
        if ($useDefault && $scope->getFallback()) {
            return $this->getScopeValue($scope->getFallback(), $attribute, $useDefault);
        }
        return $scope->getValue();
    }
}
