<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\ResourceModel\Product;

/**
 * Export-only product collection with streamed entity/attribute loading.
 */
class ExportCollection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Load entities with streamed DB cursor.
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @throws \Exception
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $this->getEntity();

        if ($this->_pageSize) {
            $this->getSelect()->limitPage($this->getCurPage(), $this->_pageSize);
        }

        $this->printLogQuery($printQuery, $logQuery);
        $query = $this->getSelect();

        try {
            $stmt = $this->getConnection()->query($query);
        } catch (\Exception $e) {
            $this->printLogQuery(false, true, $query);
            throw $e;
        }

        while ($value = $stmt->fetch()) {
            $object = $this->getNewEmptyItem()->setData($value);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = [$object];
            }
        }
        $stmt->closeCursor();

        return $this;
    }

    /**
     * Load attributes into loaded entities with streamed DB cursor.
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items) || empty($this->_itemsById) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();

        $tableAttributes = [];
        $attributeTypes = [];
        foreach ($this->_selectAttributes as $attributeCode => $attributeId) {
            if (!$attributeId) {
                continue;
            }
            $attribute = $this->_eavConfig->getAttribute($entity->getType(), $attributeCode);
            if ($attribute && !$attribute->isStatic()) {
                $tableAttributes[$attribute->getBackendTable()][] = (int)$attributeId;
                if (!isset($attributeTypes[$attribute->getBackendTable()])) {
                    $attributeTypes[$attribute->getBackendTable()] = $attribute->getBackendType();
                }
            }
        }

        $selects = [];
        foreach ($tableAttributes as $table => $attributes) {
            $select = $this->_getLoadAttributesSelect($table, $attributes);
            $selects[$attributeTypes[$table]][] = $this->_addLoadAttributesSelectValues(
                $select,
                $table,
                $attributeTypes[$table]
            );
        }
        $selectGroups = $this->_resourceHelper->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (empty($selects)) {
                continue;
            }

            $select = is_array($selects) ? implode(' UNION ALL ', $selects) : $selects;
            try {
                $stmt = $this->getConnection()->query($select);
            } catch (\Exception $e) {
                $this->printLogQuery(true, true, $select);
                throw $e;
            }

            $attributeCode = [];
            $entityIdField = $entity->getEntityIdField();
            while ($value = $stmt->fetch()) {
                $entityId = $value[$entityIdField];
                $attributeId = $value['attribute_id'];
                if (!isset($attributeCode[$attributeId])) {
                    $attributeCode[$attributeId] = array_search($attributeId, $this->_selectAttributes);
                    if (!$attributeCode[$attributeId]) {
                        $attribute = $this->_eavConfig->getAttribute($this->getEntity()->getType(), $attributeId);
                        $attributeCode[$attributeId] = $attribute->getAttributeCode();
                    }
                }

                if (!isset($this->_itemsById[$entityId])) {
                    continue;
                }
                foreach ($this->_itemsById[$entityId] as $object) {
                    $object->setData($attributeCode[$attributeId], $value['value']);
                }
            }
            $stmt->closeCursor();
        }

        return $this;
    }
}
