<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Link;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Downloadable links resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
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
        $this->_init(\Magento\Downloadable\Model\Link::class, \Magento\Downloadable\Model\ResourceModel\Link::class);
    }

    /**
     * Method for product filter
     *
     * @param \Magento\Catalog\Model\Product|array|integer|null $product
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
            if ($product instanceof \Magento\Catalog\Model\Product) {
                $this->addFieldToFilter('cpe.entity_id', $product->getEntityId());
            } else {
                $this->addFieldToFilter('cpe.entity_id', ['in' => $product]);
            }
        }

        return $this;
    }

    /**
     * Retrieve title for for current store
     *
     * @param int $storeId
     * @return $this
     */
    public function addTitleToResult($storeId = 0)
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $this->getSelect()
            ->joinLeft(
                ['d' => $this->getTable('downloadable_link_title')],
                'd.link_id = main_table.link_id AND d.store_id = 0',
                ['default_title' => 'title']
            )->joinLeft(
                ['st' => $this->getTable('downloadable_link_title')],
                'st.link_id=main_table.link_id AND st.store_id = ' . (int)$storeId,
                [
                    'store_title' => 'title',
                    'title' => $ifNullDefaultTitle
                ]
            )->order('main_table.sort_order ASC')
            ->order('title ASC');
        return $this;
    }

    /**
     * Retrieve price for for current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function addPriceToResult($websiteId)
    {
        $ifNullDefaultPrice = $this->getConnection()->getIfNullSql('stp.price', 'dp.price');
        $this->getSelect()->joinLeft(
            ['dp' => $this->getTable('downloadable_link_price')],
            'dp.link_id=main_table.link_id AND dp.website_id = 0',
            ['default_price' => 'price']
        )->joinLeft(
            ['stp' => $this->getTable('downloadable_link_price')],
            'stp.link_id=main_table.link_id AND stp.website_id = ' . (int)$websiteId,
            ['website_price' => 'price', 'price' => $ifNullDefaultPrice]
        );

        return $this;
    }
}
