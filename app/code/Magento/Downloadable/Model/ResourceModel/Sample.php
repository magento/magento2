<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Downloadable Product  Samples resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sample extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('downloadable_sample', 'sample_id');
    }

    /**
     * Save title of sample item in store scope
     *
     * @param \Magento\Downloadable\Model\Sample $sampleObject
     * @return $this
     */
    public function saveItemTitle($sampleObject)
    {
        $connection = $this->getConnection();
        $sampleTitleTable = $this->getTable('downloadable_sample_title');
        $bind = [':sample_id' => $sampleObject->getId(), ':store_id' => (int)$sampleObject->getStoreId()];
        $select = $connection->select()->from(
            $sampleTitleTable
        )->where(
            'sample_id=:sample_id AND store_id=:store_id'
        );
        if ($connection->fetchOne($select, $bind)) {
            $where = [
                'sample_id = ?' => $sampleObject->getId(),
                'store_id = ?' => (int)$sampleObject->getStoreId(),
            ];
            if ($sampleObject->getUseDefaultTitle()) {
                $connection->delete($sampleTitleTable, $where);
            } else {
                $connection->update($sampleTitleTable, ['title' => $sampleObject->getTitle()], $where);
            }
        } else {
            if (!$sampleObject->getUseDefaultTitle()) {
                $connection->insert(
                    $sampleTitleTable,
                    [
                        'sample_id' => $sampleObject->getId(),
                        'store_id' => (int)$sampleObject->getStoreId(),
                        'title' => $sampleObject->getTitle()
                    ]
                );
            }
        }
        return $this;
    }

    /**
     * Delete data by item(s)
     *
     * @param \Magento\Downloadable\Model\Sample|array|int $items
     * @return $this
     */
    public function deleteItems($items)
    {
        $connection = $this->getConnection();
        if ($items instanceof \Magento\Downloadable\Model\Sample) {
            $where = ['sample_id = ?' => $items->getId()];
        } else {
            $where = ['sample_id in (?)' => $items];
        }
        $connection->delete($this->getMainTable(), $where);
        $connection->delete($this->getTable('downloadable_sample_title'), $where);
        return $this;
    }

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        $connection = $this->getConnection();
        $ifNullDefaultTitle = $connection->getIfNullSql('st.title', 'd.title');
        $select = $connection->select()->from(
            ['m' => $this->getMainTable()],
            null
        )->join(
            ['d' => $this->getTable('downloadable_sample_title')],
            'd.sample_id=m.sample_id AND d.store_id=0',
            []
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            sprintf(
                'cpe.entity_id = m.product_id',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
            ),
            []
        )->joinLeft(
            ['st' => $this->getTable('downloadable_sample_title')],
            'st.sample_id=m.sample_id AND st.store_id=:store_id',
            ['title' => $ifNullDefaultTitle]
        )->where(
            'cpe.entity_id=:product_id',
            $productId
        );
        $bind = [':store_id' => (int)$storeId, ':product_id' => $productId];

        return $connection->fetchCol($select, $bind);
    }
}
