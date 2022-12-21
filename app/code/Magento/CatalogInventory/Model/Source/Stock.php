<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Source;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\Stock as StockModel;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * CatalogInventory Stock source model
 * @api
 * @since 100.0.2
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.4/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.4/inventory/inventory-api-reference.html
 */
class Stock extends AbstractSource
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        MetadataPool $metadataPool = null
    ) {
        $this->metadataPool = $metadataPool ?? ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        return [
            ['value' => StockModel::STOCK_IN_STOCK, 'label' => __('In Stock')],
            ['value' => StockModel::STOCK_OUT_OF_STOCK, 'label' => __('Out of Stock')]
        ];
    }

    /**
     * Add Value Sort To Collection Select.
     *
     * @param AbstractCollection $collection
     * @param string $dir
     *
     * @return $this
     * @since 100.2.4
     */
    public function addValueSortToCollection($collection, $dir = Collection::SORT_ORDER_DESC)
    {
        $productLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $collection->joinField(
            'child_id',
            $collection->getTable('catalog_product_relation'),
            'child_id',
            'parent_id=' . $productLinkField,
            null,
            'left'
        );

        $collection->joinField(
            'child_stock',
            $collection->getTable('cataloginventory_stock_item'),
            null,
            'product_id = entity_id',
            ['stock_id' => StockModel::DEFAULT_STOCK_ID],
            'left'
        );
        $collection->joinField(
            'parent_stock',
            $collection->getTable('cataloginventory_stock_item'),
            null,
            'product_id = child_id',
            ['stock_id' => StockModel::DEFAULT_STOCK_ID],
            'left'
        );

        $select = $collection->getSelect();
        $select->columns(
            'IF(SUM(`at_parent_stock`.`qty`), SUM(`at_parent_stock`.`qty`), `at_child_stock`.`qty`) as stock'
        );
        $select->group('e.entity_id');
        $select->order("stock $dir");

        return $this;
    }
}
