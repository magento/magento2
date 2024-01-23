<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model;

class ConfigurableProductsProvider
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Return list of ID for product variation
     *
     * @param array $ids
     * @return array
     */
    public function getIds(array $ids)
    {
        $key = md5(json_encode($ids)); //phpcs:ignore
        if (!isset($this->productIds[$key])) {
            $connection = $this->resource->getConnection();
            $this->productIds[$key] = $connection->fetchCol(
                $connection
                    ->select()
                    ->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['e.entity_id'])
                    ->where('e.type_id = ?', \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                    ->where('e.entity_id IN (?)', $ids, \Zend_Db::INT_TYPE)
            );
        }
        return $this->productIds[$key];
    }
}
