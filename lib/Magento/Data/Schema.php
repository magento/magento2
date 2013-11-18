<?php
/**
 *
 * @category    Magento
 * @package     Magento_Data
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Data;

class Schema extends \Magento\Object
{
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

    public function extract($rawData)
    {
        $elements = $rawData;
        return new \Magento\Data\DataArray($elements);
    }
}
