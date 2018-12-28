<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class Row reindex action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Row extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * @var Indexer
     */
    protected $flatItemWriter;

    /**
     * @var Eraser
     */
    protected $flatItemEraser;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     * @param Indexer $flatItemWriter
     * @param Eraser $flatItemEraser
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        TableBuilder $tableBuilder,
        FlatTableBuilder $flatTableBuilder,
        Indexer $flatItemWriter,
        Eraser $flatItemEraser,
        MetadataPool $metadataPool = null
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
        $this->metadataPool = $metadataPool ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * Execute row reindex action
     *
     * @param int|null $id
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $ids = [$id];
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $stores = $this->_storeManager->getStores();
        foreach ($stores as $store) {
            $tableExists = $this->_isFlatTableExists($store->getId());
            if ($tableExists) {
                $this->flatItemEraser->removeDeletedProducts($ids, $store->getId());
            }

            /* @var $status \Magento\Eav\Model\Entity\Attribute */
            $status = $this->_productIndexerHelper->getAttribute(ProductInterface::STATUS);
            $statusTable = $status->getBackend()->getTable();
            $catalogProductEntityTable = $this->_productIndexerHelper->getTable('catalog_product_entity');
            $statusConditions = [
                's.store_id IN(0,' . (int)$store->getId() . ')',
                's.attribute_id = ' . (int)$status->getId(),
                'e.entity_id = ' . (int)$id,
            ];
            $select = $this->_connection->select();
            $select->from(['e' => $catalogProductEntityTable], ['s.value'])
                ->where(implode(' AND ', $statusConditions))
                ->joinLeft(['s' => $statusTable], "e.{$linkField} = s.{$linkField}", [])
                ->order('s.store_id DESC')
                ->limit(1);
            $result = $this->_connection->query($select);
            $status = $result->fetchColumn(0);

            if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                if (!$tableExists) {
                    $this->_flatTableBuilder->build(
                        $store->getId(),
                        $ids,
                        $this->_valueFieldSuffix,
                        $this->_tableDropSuffix,
                        false
                    );
                }
                $this->flatItemWriter->write($store->getId(), $id, $this->_valueFieldSuffix);
            } else {
                $this->flatItemEraser->deleteProductsFromStore($id, $store->getId());
            }
        }

        return $this;
    }
}
