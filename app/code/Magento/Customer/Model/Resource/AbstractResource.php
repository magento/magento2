<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Model\Resource;

abstract class AbstractResource extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * {@inheritdoc}
     *
     * Redeclare method to disable entity_type_id filter
     */
    protected function _saveAttribute($object, $attribute, $value)
    {
        $table = $attribute->getBackend()->getTable();
        if (!isset($this->_attributeValuesToSave[$table])) {
            $this->_attributeValuesToSave[$table] = [];
        }

        $entityIdField = $attribute->getBackend()->getEntityIdField();

        $data = [
            $entityIdField => $object->getId(),
            'attribute_id' => $attribute->getId(),
            'value' => $this->_prepareValueForSave($value, $attribute),
        ];

        $this->_attributeValuesToSave[$table][] = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Redeclare method to disable entity_type_id filter
     */
    public function save(\Magento\Framework\Object $object)
    {
        /**
         * Direct deleted items to delete method
         */
        if ($object->isDeleted()) {
            return $this->delete($object);
        }
        if (!$object->hasDataChanges()) {
            return $this;
        }
        $this->beginTransaction();
        try {
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                if (!$this->isPartialSave()) {
                    $this->loadAllAttributes($object);
                }

                $object->setParentId((int)$object->getParentId());

                $this->_beforeSave($object);
                $data = $this->_collectSaveData($object);
                $this->_processSaveData($data);
                $this->_afterSave($object);

                $object->afterSave();
            }
            $this->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (\Exception $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw $e;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Redeclare method to disable entity_type_id filter
     */
    protected function getAttributeRow($entity, $object, $attribute)
    {
        return [
            'attribute_id' => $attribute->getId(),
            $entity->getEntityIdField() => $object->getData($entity->getEntityIdField()),
        ];
    }
}
