<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model;

/**
 * Class \Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider
 *
 * @since 2.0.0
 */
class ConfigurableProductsProvider
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var array
     * @since 2.1.0
     */
    private $productIds = [];

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param array $ids
     * @return array
     * @since 2.0.0
     */
    public function getIds(array $ids)
    {
        $key =  md5(json_encode($ids));
        if (!isset($this->productIds[$key])) {
            $connection = $this->resource->getConnection();
            $this->productIds[$key] = $connection->fetchCol(
                $connection
                    ->select()
                    ->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['e.entity_id'])
                    ->where('e.type_id = ?', \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                    ->where('e.entity_id IN (?)', $ids)
            );
        }
        return $this->productIds[$key];
    }
}
