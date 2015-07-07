<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup;


use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $this->setWeight('sku', 6, $connection);
        $this->setWeight('name', 5, $connection);
    }


    /**
     * @param string $attributeCode
     * @param int $weight
     * @param AdapterInterface $connection
     * @internal param $oldWeight
     */
    private function setWeight($attributeCode, $weight, AdapterInterface $connection)
    {
        $updateQuery = 'UPDATE catalog_eav_attribute SET search_weight = ?'
            . ' WHERE attribute_id ='
            . ' (SELECT eav_attribute.attribute_id FROM eav_attribute'
            . ' LEFT JOIN eav_entity_type ON eav_attribute.entity_type_id = eav_entity_type.entity_type_id'
            . ' WHERE eav_entity_type.entity_type_code = ?'
            . ' AND eav_attribute.attribute_code = ?)';
        $bindings = [$weight, 'catalog_product', $attributeCode];

        $connection->query($updateQuery, $bindings);
    }
}
