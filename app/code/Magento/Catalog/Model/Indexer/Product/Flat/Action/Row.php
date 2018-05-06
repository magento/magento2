<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder;

/**
 * Class Row reindex action
 */
class Row extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer
     */
    protected $flatItemWriter;

    /**
     * @var Eraser
     */
    protected $flatItemEraser;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     * @param Indexer $flatItemWriter
     * @param Eraser $flatItemEraser
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        TableBuilder $tableBuilder,
        FlatTableBuilder $flatTableBuilder,
        Indexer $flatItemWriter,
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
        $this->flatItemWriter = $flatItemWriter;
        $this->flatItemEraser = $flatItemEraser;
    }

    /**
     * Execute row reindex action
     *
     * @param int|null $id
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $ids = [$id];
        foreach ($this->_storeManager->getStores() as $store) {
            $tableExists = $this->_isFlatTableExists($store->getId());
            if ($tableExists) {
                $this->flatItemEraser->removeDeletedProducts($ids, $store->getId());
            }

            /* @var $status \Magento\Eav\Model\Entity\Attribute */
            $status = $this->_productIndexerHelper->getAttribute('status');
            $statusTable = $status->getBackend()->getTable();
            $statusConditions = [
                'store_id IN(0,' . (int)$store->getId() . ')',
                'attribute_id = ' . (int)$status->getId(),
                'entity_id = ' . (int)$id
            ];
            $select = $this->_connection->select();
            $select->from(
                $statusTable,
                ['value']
            )->where(
                implode(' AND ', $statusConditions)
            )->order(
                'store_id DESC'
            );
            $result = $this->_connection->query($select);
            $status = $result->fetch(1);

            if ($status['value'] == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                if (isset($ids[0])) {
                    if (!$tableExists) {
                        $this->_flatTableBuilder->build(
                            $store->getId(),
                            [$ids[0]],
                            $this->_valueFieldSuffix,
                            $this->_tableDropSuffix,
                            false
                        );
                    }
                    $this->flatItemWriter->write($store->getId(), $ids[0], $this->_valueFieldSuffix);
                }
            }
            if ($status['value'] == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
                $this->flatItemEraser->deleteProductsFromStore($id, $store->getId());
            }
        }
        return $this;
    }
}
