<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Catalog Product Mass processing resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Magento\Catalog\Model\ResourceModel\AbstractResource
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->setConnection(
            $resource->getConnection('catalog')
        );
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return $this
     * @throws \Exception
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        $object = new \Magento\Framework\DataObject();
        $object->setStoreId($storeId);

        $this->getConnection()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {
                $attribute = $this->getAttribute($attrCode);
                if (!$attribute->getAttributeId()) {
                    continue;
                }

                $i = 0;
                foreach ($entityIds as $entityId) {
                    $i++;
                    $object->setId($entityId);
                    $object->setEntityId($entityId);
                    // collect data for save
                    $this->_saveAttributeValue($object, $attribute, $value);
                    // save collected data every 1000 rows
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                }
                $this->_processAttributeValues();
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Insert or Update attribute data
     *
     * @param \Magento\Catalog\Model\AbstractModel $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     * @since 2.1.0
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $connection = $this->getConnection();
        $storeId = (int) $this->_storeManager->getStore($object->getStoreId())->getId();
        $table = $attribute->getBackend()->getTable();

        $entityId = $this->resolveEntityId($object->getId(), $table);

        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        if ($this->_storeManager->hasSingleStore()) {
            $storeId = $this->getDefaultStoreId();
            $connection->delete(
                $table,
                [
                    'attribute_id = ?' => $attribute->getAttributeId(),
                    $this->getLinkField() . ' = ?' => $entityId,
                    'store_id <> ?' => $storeId
                ]
            );
        }

        $data = new \Magento\Framework\DataObject(
            [
                'attribute_id' => $attribute->getAttributeId(),
                'store_id' => $storeId,
                $this->getLinkField() => $entityId,
                'value' => $this->_prepareValueForSave($value, $attribute),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $table);

        if ($attribute->isScopeStore()) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$table][] = $bind;
        } elseif ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
            /**
             * Update attribute value for website
             */
            $storeIds = $this->_storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int) $storeId;
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        } else {
            /**
             * Update global attribute value
             */
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$table][] = $bind;
        }

        return $this;
    }

    /**
     * @param int $entityId
     * @return int
     * @since 2.1.0
     */
    protected function resolveEntityId($entityId)
    {
        if ($this->getIdFieldName() == $this->getLinkField()) {
            return $entityId;
        }
        $select = $this->getConnection()->select();
        $tableName = $this->_resource->getTableName('catalog_product_entity');
        $select->from($tableName, [$this->getLinkField()])
            ->where('entity_id = ?', $entityId);
        return $this->getConnection()->fetchOne($select);
    }
}
