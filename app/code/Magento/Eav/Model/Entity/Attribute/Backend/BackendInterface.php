<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * Entity attribute backend interface
 *
 * Backend is responsible for saving the values of the attribute
 * and performing pre and post actions
 *
 */
interface BackendInterface
{
    /**
     * @return string
     */
    public function getTable();

    /**
     * @return bool
     */
    public function isStatic();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getEntityIdField();

    /**
     * @param int $valueId
     * @return $this
     */
    public function setValueId($valueId);

    /**
     * @return int
     */
    public function getValueId();

    /**
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterLoad($object);

    /**
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object);

    /**
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterSave($object);

    /**
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeDelete($object);

    /**
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterDelete($object);

    /**
     * Get entity value id
     *
     * @param \Magento\Framework\Object $entity
     * @return int
     */
    public function getEntityValueId($entity);

    /**
     * Set entity value id
     *
     * @param \Magento\Framework\Object $entity
     * @param int $valueId
     * @return $this
     */
    public function setEntityValueId($entity, $valueId);

    /**
     * Whether an attribute is represented by a scalar value that can be stored in a generic way
     *
     * @return bool
     */
    public function isScalar();
}
