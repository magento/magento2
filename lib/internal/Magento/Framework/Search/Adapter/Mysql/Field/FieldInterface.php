<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Field;


interface FieldInterface
{
    const TYPE_FLAT = 1;
    const TYPE_FULLTEXT = 2;

    /**
     * Get type of index
     * @return int
     */
    public function getType();

    /**
     * Get ID of attribute
     * @return int
     */
    public function getAttributeId();

    /**
     * Get field name
     * @return string
     */
    public function getColumn();
}
