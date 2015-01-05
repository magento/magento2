<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * System Configuration Ignore Mapper
 */
namespace Magento\Backend\Model\Config\Structure\Mapper;

class Ignore implements \Magento\Backend\Model\Config\Structure\MapperInterface
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data)
    {
        return $data;
    }
}
