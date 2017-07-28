<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * Entity attribute backend interface
 *
 * Backend is responsible for saving the values of the attribute
 * and performing pre and post actions
 *
 * @since 2.0.0
 */
interface BackendInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getTable();

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isStatic();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getEntityIdField();

    /**
     * @param int $valueId
     * @return $this
     * @since 2.0.0
     */
    public function setValueId($valueId);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getValueId();

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function afterLoad($object);

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave($object);

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function afterSave($object);

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function beforeDelete($object);

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function afterDelete($object);

    /**
     * Get entity value id
     *
     * @param \Magento\Framework\DataObject $entity
     * @return int
     * @since 2.0.0
     */
    public function getEntityValueId($entity);

    /**
     * Set entity value id
     *
     * @param \Magento\Framework\DataObject $entity
     * @param int $valueId
     * @return $this
     * @since 2.0.0
     */
    public function setEntityValueId($entity, $valueId);

    /**
     * Whether an attribute is represented by a scalar value that can be stored in a generic way
     *
     * @return bool
     * @since 2.0.0
     */
    public function isScalar();
}
