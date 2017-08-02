<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder;

/**
 * Class Rows reindex action for mass actions
 *
 * @since 2.0.0
 */
class Rows extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * @var Eraser
     * @since 2.0.0
     */
    protected $flatItemEraser;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     * @param Eraser $flatItemEraser
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        TableBuilder $tableBuilder,
        FlatTableBuilder $flatTableBuilder,
        Eraser $flatItemEraser
    ) {
        parent::__construct(
            $resource,
            $storeManager,
            $productHelper,
            $productType,
            $tableBuilder,
            $flatTableBuilder
        );
        $this->flatItemEraser = $flatItemEraser;
    }

    /**
     * Execute multiple rows reindex action
     *
     * @param array $ids
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Bad value was supplied.'));
        }
        foreach ($this->_storeManager->getStores() as $store) {
            $tableExists = $this->_isFlatTableExists($store->getId());
            $idsBatches = array_chunk($ids, \Magento\Catalog\Helper\Product\Flat\Indexer::BATCH_SIZE);
            foreach ($idsBatches as $changedIds) {
                if ($tableExists) {
                    $this->flatItemEraser->removeDeletedProducts($changedIds, $store->getId());
                }
                if (!empty($changedIds)) {
                    $this->_reindex($store->getId(), $changedIds);
                }
            }
        }
        return $this;
    }
}
