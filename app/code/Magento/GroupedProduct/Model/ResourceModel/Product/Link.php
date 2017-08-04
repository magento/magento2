<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @api
 */
class Link extends \Magento\Catalog\Model\ResourceModel\Product\Link
{
    const LINK_TYPE_GROUPED = 3;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * Link constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Relation $catalogProductRelation
     * @param MetadataPool $metadataPool
     * @param string|null $connectionName
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Relation $catalogProductRelation,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $context,
            $catalogProductRelation,
            $connectionName
        );
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param int $typeId
     * @return array
     */
    public function getChildrenIds($parentId, $typeId)
    {
        $connection = $this->getConnection();
        $childrenIds = [];
        $bind = [':product_id' => (int)$parentId, ':link_type_id' => (int)$typeId];
        $select = $connection->select()->from(
            ['l' => $this->getMainTable()],
            ['linked_product_id']
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            sprintf(
                'cpe.%s = l.product_id',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
            )
        )->where(
            'cpe.entity_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );

        $select->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.linked_product_id AND e.required_options = 0',
            []
        );

        $childrenIds[$typeId] = [];
        $result = $connection->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[$typeId][$row['linked_product_id']] = $row['linked_product_id'];
        }

        return $childrenIds;
    }
}
