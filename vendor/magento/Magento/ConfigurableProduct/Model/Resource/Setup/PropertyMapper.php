<?php
/**
 * Configurable product attribute property mapper
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ConfigurableProduct\Model\Resource\Setup;

use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

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
     */
    public function map(array $input, $entityTypeId)
    {
        return ['is_configurable' => $this->_getValue($input, 'is_configurable', 1)];
    }
}
