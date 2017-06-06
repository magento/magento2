<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * CatalogSearch Fulltext Index resource model
 */
class Fulltext extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Holder for MetadataPool instance.
     *
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string $connectionName
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null,
        MetadataPool $metadataPool = null
    ) {
        $this->_eventManager = $eventManager;
        $this->metadataPool = $metadataPool ? : ObjectManager::getInstance()->get(MetadataPool::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_fulltext', 'product_id');
    }

    /**
     * Reset search results
     *
     * @return $this
     */
    public function resetSearchResults()
    {
        $connection = $this->getConnection();
        $connection->update($this->getTable('search_query'), ['is_processed' => 0], ['is_processed != 0']);
        $this->_eventManager->dispatch('catalogsearch_reset_search_result');
        return $this;
    }

    /**
     * Retrieve product relations by children.
     *
     * @param int|array $childIds
     * @return array
     */
    public function getRelationsByChild($childIds)
    {
        $connection = $this->getConnection();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['relation' => $this->getTable('catalog_product_relation')],
            []
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            'cpe.' . $linkField . ' = relation.parent_id',
            ['cpe.entity_id']
        )->where(
            'relation.child_id IN (?)',
            $childIds
        )->distinct(true);

        return $connection->fetchCol($select);
    }
}
