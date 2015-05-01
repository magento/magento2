<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface TaxClassInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     *
     * Tax class field key.
     */
    const KEY_ID = 'class_id';
    const KEY_NAME = 'class_name';
    const KEY_TYPE = 'class_type';
    /**#@-*/

    /**
     * Get tax class ID.
     *
     * @api
     * @return int|null
     */
    public function getClassId();

    /**
     * Set tax class ID.
     *
     * @api
     * @param int $classId
     * @return $this
     */
    public function setClassId($classId);

    /**
     * Get tax class name.
     *
     * @api
     * @return string
     */
    public function getClassName();

    /**
     * Set tax class name.
     *
     * @api
     * @param string $className
     * @return $this
     */
    public function setClassName($className);

    /**
     * Get tax class type.
     *
     * @api
     * @return string
     */
    public function getClassType();

    /**
     * Set tax class type.
     *
     * @api
     * @param string $classType
     * @return $this
     */
    public function setClassType($classType);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxClassExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxClassExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxClassExtensionInterface $extensionAttributes);
}
