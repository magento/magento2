<?php
/**
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Data;

class Schema extends \Magento\Framework\Object
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
