<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class \Magento\Framework\Data\Schema
 *
 * @since 2.0.0
 */
class Schema extends \Magento\Framework\DataObject
{
    /**
     * @param mixed $schema
     * @return void
     * @since 2.0.0
     */
    public function load($schema)
    {
        if (is_array($schema)) {
            $this->setData($schema);
        } elseif (is_string($schema)) {
            if (is_file($schema)) {
                include $schema;
                $this->setData($schema);
            }
        }
    }

    /**
     * @param mixed $rawData
     * @return DataArray
     * @since 2.0.0
     */
    public function extract($rawData)
    {
        $elements = $rawData;
        return new DataArray($elements);
    }
}
