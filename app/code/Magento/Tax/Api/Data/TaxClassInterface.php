<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Tax class interface.
 * @api
 * @since 2.0.0
 */
interface TaxClassInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get tax class ID.
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getClassId();

    /**
     * Set tax class ID.
     *
     * @param int $classId
     * @return $this
     * @since 2.0.0
     */
    public function setClassId($classId);

    /**
     * Get tax class name.
     *
     * @return string
     * @since 2.0.0
     */
    public function getClassName();

    /**
     * Set tax class name.
     *
     * @param string $className
     * @return $this
     * @since 2.0.0
     */
    public function setClassName($className);

    /**
     * Get tax class type.
     *
     * @return string
     * @since 2.0.0
     */
    public function getClassType();

    /**
     * Set tax class type.
     *
     * @param string $classType
     * @return $this
     * @since 2.0.0
     */
    public function setClassType($classType);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxClassExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxClassExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxClassExtensionInterface $extensionAttributes);
}
