<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Price;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\SpecialPriceInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Special price resource.
 */
class SpecialPrice implements \Magento\Catalog\Api\SpecialPriceInterface
{
    /**
     * Price storage table.
     *
     * @var string
     */
    private $priceTable = 'catalog_product_entity_decimal';

    /**
     * Datetime storage table.
     *
     * @var string
     */
    private $datetimeTable = 'catalog_product_entity_datetime';

    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Special Price attribute ID.
     *
     * @var int
     */
    private $priceAttributeId;

    /**
     * Special price from attribute ID.
     *
     * @var int
     */
    private $priceFromAttributeId;

    /**
     * Special price to attribute ID.
     *
     * @var int
     */
    private $priceToAttributeId;

    /**
     * Items per operation.
     *
     * @var int
     */
    private $itemsPerOperation = 500;

    /**
     * @param Attribute $attributeResource
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param ProductIdLocatorInterface $productIdLocator
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Attribute $attributeResource,
        ProductAttributeRepositoryInterface $attributeRepository,
        ProductIdLocatorInterface $productIdLocator,
        MetadataPool $metadataPool,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeRepository = $attributeRepository;
        $this->productIdLocator = $productIdLocator;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function get(array $skus)
    {
        $ids = $this->retrieveAffectedIds($skus);
        $priceTable = $this->attributeResource->getTable($this->priceTable);
        $dateTimeTable = $this->attributeResource->getTable($this->datetimeTable);
        $linkField = $this->getEntityLinkField();
        $select = $this->attributeResource->getConnection()
            ->select()
            ->from(
                $priceTable,
                [
                    'value_id',
                    'store_id',
                    $this->getEntityLinkField(),
                    'value',
                ]
            )
            ->joinLeft(
                $dateTimeTable . ' AS datetime_from',
                $priceTable . '.' . $linkField . '=' . 'datetime_from.' . $linkField
                . ' AND datetime_from.attribute_id=' . $this->getPriceFromAttributeId(),
                'value AS price_from'
            )
            ->joinLeft(
                $dateTimeTable . ' AS datetime_to',
                $priceTable . '.' . $linkField . '=' . 'datetime_to.' . $linkField
                . ' AND datetime_to.attribute_id=' . $this->getPriceToAttributeId(),
                'value AS price_to'
            )
            ->where($priceTable . '.' . $linkField . ' IN (?)', $ids)
            ->where($priceTable . '.attribute_id = ?', $this->getPriceAttributeId());

        return $this->attributeResource->getConnection()->fetchAll($select);
    }

    /**
     * @inheritDoc
     */
    public function update(array $prices)
    {
        $formattedPrices = [];
        $formattedDates = [];

        /** @var SpecialPriceInterface $price */
        foreach ($prices as $price) {
            $productIdsBySku = $this->productIdLocator->retrieveProductIdsBySkus([$price->getSku()]);
            $ids = array_keys($productIdsBySku[$price->getSku()]);
            foreach ($ids as $id) {
                $formattedPrices[] = [
                    'store_id' => $price->getStoreId(),
                    $this->getEntityLinkField() => $id,
                    'value' => $price->getPrice(),
                    'attribute_id' => $this->getPriceAttributeId(),
                ];
                if ($price->getPriceFrom()) {
                    $formattedDates[] = [
                        'store_id' => $price->getStoreId(),
                        $this->getEntityLinkField() => $id,
                        'value' => $price->getPriceFrom(),
                        'attribute_id' => $this->getPriceFromAttributeId(),
                    ];
                }
                if ($price->getPriceTo()) {
                    $formattedDates[] = [
                        'store_id' => $price->getStoreId(),
                        $this->getEntityLinkField() => $id,
                        'value' => $price->getPriceTo(),
                        'attribute_id' => $this->getPriceToAttributeId(),
                    ];
                }
            }
        }
        $connection = $this->attributeResource->getConnection();
        $connection->beginTransaction();

        try {
            $this->updateItems($formattedPrices, $this->priceTable);
            $this->updateItems($formattedDates, $this->datetimeTable);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw new CouldNotSaveException(__('Could not save Prices.'), $e);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete(array $prices)
    {
        $skusByStore = [];
        foreach ($prices as $priceItem) {
            $skusByStore[$priceItem->getStoreId()][] = $priceItem->getSku();
        }

        $stores = $this->storeManager->getStores();
        $storesByWebsite = [];
        foreach ($stores as $store) {
            $storesByWebsite[$store->getWebsiteId()][] = $store->getId();
        }

        $connection = $this->attributeResource->getConnection();
        foreach ($skusByStore as $storeId => $skus) {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $storeIds = $storesByWebsite[$websiteId] ?? [$storeId];

            $skus = array_unique($skus);
            $ids = $this->retrieveAffectedIds($skus);
            $connection->beginTransaction();
            try {
                foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                    $this->attributeResource->getConnection()->delete(
                        $this->attributeResource->getTable($this->priceTable),
                        [
                            'attribute_id = ?' => $this->getPriceAttributeId(),
                            'store_id IN (?)' => $storeIds,
                            $this->getEntityLinkField() . ' IN (?)' => $idsBunch
                        ]
                    );
                }
                foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                    $this->attributeResource->getConnection()->delete(
                        $this->attributeResource->getTable($this->datetimeTable),
                        [
                            'attribute_id IN (?)' => [$this->getPriceFromAttributeId(), $this->getPriceToAttributeId()],
                            'store_id IN (?)' => $storeIds,
                            $this->getEntityLinkField() . ' IN (?)' => $idsBunch
                        ]
                    );
                }
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
                throw new CouldNotDeleteException(__('Could not delete Prices'), $e);
            }
        }

        return true;
    }

    /**
     * Get link field.
     *
     * @return string
     */
    public function getEntityLinkField()
    {
        return $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }

    /**
     * Update items in database.
     *
     * @param array $items
     * @param string $table
     *
     * @return void
     */
    private function updateItems(array $items, $table)
    {
        foreach (array_chunk($items, $this->itemsPerOperation) as $itemsBunch) {
            $this->attributeResource->getConnection()->insertOnDuplicate(
                $this->attributeResource->getTable($table),
                $itemsBunch,
                ['value']
            );
        }
    }

    /**
     * Get special price attribute ID.
     *
     * @return int
     */
    private function getPriceAttributeId()
    {
        if (!$this->priceAttributeId) {
            $this->priceAttributeId = $this->attributeRepository->get('special_price')->getAttributeId();
        }

        return $this->priceAttributeId;
    }

    /**
     * Get special price from attribute ID.
     *
     * @return int
     */
    private function getPriceFromAttributeId()
    {
        if (!$this->priceFromAttributeId) {
            $this->priceFromAttributeId = $this->attributeRepository->get('special_from_date')->getAttributeId();
        }

        return $this->priceFromAttributeId;
    }

    /**
     * Get special price to attribute ID.
     *
     * @return int
     */
    private function getPriceToAttributeId()
    {
        if (!$this->priceToAttributeId) {
            $this->priceToAttributeId = $this->attributeRepository->get('special_to_date')->getAttributeId();
        }

        return $this->priceToAttributeId;
    }

    /**
     * Retrieve IDs of products, that were affected during price update.
     *
     * @param array $skus
     *
     * @return array
     */
    private function retrieveAffectedIds(array $skus)
    {
        $affectedIds = [];

        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $productIds) {
            $affectedIds[] = array_keys($productIds);
        }

        return array_unique(array_merge([], ...$affectedIds));
    }
}
