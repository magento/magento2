<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return int|null
     */
    public function getId();

    /**
     * Get code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get tax class id
     *
     * @return int
     */
    public function getTaxClassId();

    /**
     * Get tax class name
     *
     * @return string|null
     */
    public function getTaxClassName();
}
