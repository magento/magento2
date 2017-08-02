<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * Interface \Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface
 *
 * @since 2.0.0
 */
interface FieldInterface
{
    const TYPE_FLAT = 1;
    const TYPE_FULLTEXT = 2;

    /**
     * Get type of index
     * @return int
     * @since 2.0.0
     */
    public function getType();

    /**
     * Get ID of attribute
     * @return int
     * @since 2.0.0
     */
    public function getAttributeId();

    /**
     * Get field name
     * @return string
     * @since 2.0.0
     */
    public function getColumn();
}
