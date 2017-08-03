<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer group interface.
 * @api
 * @since 2.0.0
 */
interface GroupInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const ID = 'id';
    const CODE = 'code';
    const TAX_CLASS_ID = 'tax_class_id';
    const TAX_CLASS_NAME = 'tax_class_name';
    const NOT_LOGGED_IN_ID = 0;
    const CUST_GROUP_ALL = 32000;
    const GROUP_CODE_MAX_LENGTH = 32;
    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get tax class id
     *
     * @return int
     * @since 2.0.0
     */
    public function getTaxClassId();

    /**
     * Set tax class id
     *
     * @param int $taxClassId
     * @return $this
     * @since 2.0.0
     */
    public function setTaxClassId($taxClassId);

    /**
     * Get tax class name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTaxClassName();

    /**
     * Set tax class name
     *
     * @param string $taxClassName
     * @return string|null
     * @since 2.0.0
     */
    public function setTaxClassName($taxClassName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Customer\Api\Data\GroupExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Customer\Api\Data\GroupExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\GroupExtensionInterface $extensionAttributes);
}
