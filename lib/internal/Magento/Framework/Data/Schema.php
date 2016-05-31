<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

class Schema extends \Magento\Framework\DataObject
{
    /**
     * @param mixed $schema
     * @return void
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
     */
    public function extract($rawData)
    {
        $elements = $rawData;
        return new DataArray($elements);
    }
}
