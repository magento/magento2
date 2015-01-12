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
