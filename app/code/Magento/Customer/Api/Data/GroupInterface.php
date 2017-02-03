<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer group interface.
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
     * @api
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get code
     *
     * @api
     * @return string
     */
    public function getCode();

    /**
     * Set code
     *
     * @api
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get tax class id
     *
     * @api
     * @return int
     */
    public function getTaxClassId();

    /**
     * Set tax class id
     *
     * @api
     * @param int $taxClassId
     * @return $this
     */
    public function setTaxClassId($taxClassId);

    /**
     * Get tax class name
     *
     * @api
     * @return string|null
     */
    public function getTaxClassName();

    /**
     * Set tax class name
     *
     * @api
     * @param string $taxClassName
     * @return string|null
     */
    public function setTaxClassName($taxClassName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Customer\Api\Data\GroupExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Customer\Api\Data\GroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\GroupExtensionInterface $extensionAttributes);
}
