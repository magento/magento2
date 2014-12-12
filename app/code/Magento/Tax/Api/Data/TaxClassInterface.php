<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @return int|null
     */
    public function getClassId();

    /**
     * Get tax class name.
     *
     * @return string
     */
    public function getClassName();

    /**
     * Get tax class type.
     *
     * @return string
     */
    public function getClassType();
}
