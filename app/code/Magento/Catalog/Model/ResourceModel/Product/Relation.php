<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Catalog Product Relations Resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Relation extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Context $context
     * @param string $connectionName
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Context $context,
        $connectionName = null,
        MetadataPool $metadataPool = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * Initialize resource model and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_relation', 'parent_id');
    }

    /**
     * Save (rebuild) product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     */
    public function processRelations($parentId, $childIds)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['child_id']
        )->where(
            'parent_id = ?',
            $parentId
        );
        $old = $this->getConnection()->fetchCol($select);
        $new = $childIds;

        $insert = array_diff($new, $old);
        $delete = array_diff($old, $new);

        $this->addRelations($parentId, $insert);
        $this->removeRelations($parentId, $delete);

        return $this;
    }

    /**
     * Add Relation on duplicate update
     *
     * @param int $parentId
     * @param int $childId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addRelation($parentId, $childId)
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            ['parent_id' => $parentId, 'child_id' => $childId]
        );
        return $this;
    }

    /**
     * Add Relations
     *
     * @param int $parentId
     * @param int[] $childIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addRelations($parentId, $childIds)
    {
        if (!empty($childIds)) {
            $insertData = [];
            foreach ($childIds as $childId) {
                $insertData[] = ['parent_id' => $parentId, 'child_id' => $childId];
            }
            $this->getConnection()->insertMultiple($this->getMainTable(), $insertData);
        }
        return $this;
    }

    /**
     * Remove Relations
     *
     * @param int $parentId
     * @param int[] $childIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeRelations($parentId, $childIds)
    {
        if (!empty($childIds)) {
            $where = join(
                ' AND ',
                [
                    $this->getConnection()->quoteInto('parent_id = ?', $parentId),
                    $this->getConnection()->quoteInto('child_id IN(?)', $childIds)
                ]
            );
            $this->getConnection()->delete($this->getMainTable(), $where);
        }
        return $this;
    }

    /**
     * Finds parent relations by given children ids.
     *
     * @param array $childrenIds Child products entity ids.
     * @return array Parent products entity ids.
     */
    public function getRelationsByChildren(array $childrenIds): array
    {
        $connection = $this->getConnection();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)
            ->getLinkField();
        $select = $connection->select()
            ->from(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'entity_id'
            )->join(
                ['relation' => $this->getTable('catalog_product_relation')],
                'relation.parent_id = cpe.' . $linkField
            )->where('relation.child_id IN(?)', $childrenIds);

        return $connection->fetchCol($select);
    }
}
