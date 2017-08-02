<?php
/**
 * Configurable product attribute property mapper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Setup;

use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

/**
 * Class \Magento\ConfigurableProduct\Model\ResourceModel\Setup\PropertyMapper
 *
 * @since 2.0.0
 */
class PropertyMapper extends PropertyMapperAbstract
{
    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return array
     * @since 2.0.0
     */
    public function map(array $input, $entityTypeId)
    {
        return ['is_configurable' => $this->_getValue($input, 'is_configurable', 1)];
    }
}
