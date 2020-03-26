<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product\Flat\Indexer as FlatIndexer;
use Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction;
use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ResourceModel\Store\Collection;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Row reindex action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Row extends AbstractAction
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
     * @var Link
     */
    private $productWebsiteLink;

    /**
     * @var CollectionFactory
     */
    private $storeCollectionFactory;

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param FlatIndexer $productHelper
     * @param Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     * @param Indexer $flatItemWriter
     * @param Eraser $flatItemEraser
     * @param MetadataPool|null $metadataPool
     * @param Link|null $productWebsiteLink
     * @param CollectionFactory|null $storeCollectionFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        FlatIndexer $productHelper,
        Type $productType,
        TableBuilder $tableBuilder,
        FlatTableBuilder $flatTableBuilder,
        Indexer $flatItemWriter,
        Eraser $flatItemEraser,
        MetadataPool $metadataPool = null,
        Link $productWebsiteLink = null,
        CollectionFactory $storeCollectionFactory = null
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
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
        $this->productWebsiteLink = $productWebsiteLink ?: ObjectManager::getInstance()->get(Link::class);
        $this->storeCollectionFactory = $storeCollectionFactory ?:
            ObjectManager::getInstance()->get(CollectionFactory::class);
    }

    /**
     * Execute row reindex action
     *
     * @param int|null $id
     * @return Row
     * @throws LocalizedException
     */
    public function execute($id = null)
    {
        if (!isset($id)) {
            throw new LocalizedException(__('We can\'t rebuild the index for an undefined product.'));
        }
        $ids = [$id];
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productWebsites = $this->productWebsiteLink->getWebsiteIdsByProductId($id);

        foreach ($this->getStoresByWebsiteIds($productWebsites) as $store) {
            $tableExists = $this->_isFlatTableExists($store->getId());
            if ($tableExists) {
                $this->flatItemEraser->removeDeletedProducts($ids, $store->getId());
                $this->flatItemEraser->removeDisabledProducts($ids, $store->getId());
            }

            /* @var $status Attribute */
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

            if ((int) $status === Status::STATUS_ENABLED) {
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

    /**
     * Stores collection by website id's
     *
     * @param array $websiteIds
     * @return Collection
     */
    private function getStoresByWebsiteIds(array $websiteIds): Collection
    {
        return $this->storeCollectionFactory->create()
            ->addWebsiteFilter($websiteIds);
    }
}
