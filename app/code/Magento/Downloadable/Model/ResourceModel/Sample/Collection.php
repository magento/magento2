<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Sample;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Downloadable samples resource collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     * @since 100.1.0
     */
    protected $metadataPool;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Downloadable\Model\Sample::class,
            \Magento\Downloadable\Model\ResourceModel\Sample::class
        );
    }

    /**
     * Method for product filter
     *
     * @param \Magento\Catalog\Model\Product|array|int|null $product
     * @return $this
     */
    public function addProductToFilter($product)
    {
        if (empty($product)) {
            $this->addFieldToFilter('product_id', '');
        } else {
            $this->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                sprintf(
                    'cpe.%s = main_table.product_id',
                    $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
                )
            );
            if (is_array($product)) {
                $this->addFieldToFilter('cpe.entity_id', ['in' => $product]);
            } else {
                $this->addFieldToFilter('cpe.entity_id', $product);
            }
        }

        return $this;
    }

    /**
     * Add title column to select
     *
     * @param int $storeId
     * @return $this
     */
    public function addTitleToResult($storeId = 0)
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $this->getSelect()->joinLeft(
            ['d' => $this->getTable('downloadable_sample_title')],
            'd.sample_id=main_table.sample_id AND d.store_id = 0',
            ['default_title' => 'title']
        )->joinLeft(
            ['st' => $this->getTable('downloadable_sample_title')],
            'st.sample_id=main_table.sample_id AND st.store_id = ' . (int)$storeId,
            ['store_title' => 'title', 'title' => $ifNullDefaultTitle]
        )->order(
            'main_table.sort_order ASC'
        )->order(
            'title ASC'
        );

        return $this;
    }
}
