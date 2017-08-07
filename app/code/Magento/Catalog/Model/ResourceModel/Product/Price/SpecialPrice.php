<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Price;

/**
 * Special price resource.
 * @since 2.2.0
 */
class SpecialPrice implements \Magento\Catalog\Api\SpecialPriceInterface
{
    /**
     * Price storage table.
     *
     * @var string
     * @since 2.2.0
     */
    private $priceTable = 'catalog_product_entity_decimal';

    /**
     * Datetime storage table.
     *
     * @var string
     * @since 2.2.0
     */
    private $datetimeTable = 'catalog_product_entity_datetime';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     * @since 2.2.0
     */
    private $attributeResource;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     * @since 2.2.0
     */
    private $attributeRepository;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     * @since 2.2.0
     */
    private $productIdLocator;

    /**
     * Metadata pool.
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * Special Price attribute ID.
     *
     * @var int
     * @since 2.2.0
     */
    private $priceAttributeId;

    /**
     * Special price from attribute ID.
     *
     * @var int
     * @since 2.2.0
     */
    private $priceFromAttributeId;

    /**
     * Special price to attribute ID.
     *
     * @var int
     * @since 2.2.0
     */
    private $priceToAttributeId;

    /**
     * Items per operation.
     *
     * @var int
     * @since 2.2.0
     */
    private $itemsPerOperation = 500;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeRepository = $attributeRepository;
        $this->productIdLocator = $productIdLocator;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function update(array $prices)
    {
        $formattedPrices = [];
        $formattedDates = [];

        /** @var \Magento\Catalog\Api\Data\SpecialPriceInterface $price */
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
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save Prices.'),
                $e
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function delete(array $prices)
    {
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );
        $ids = $this->retrieveAffectedIds($skus);
        $connection = $this->attributeResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                $this->attributeResource->getConnection()->delete(
                    $this->attributeResource->getTable($this->priceTable),
                    [
                        'attribute_id = ?' => $this->getPriceAttributeId(),
                        $this->getEntityLinkField() . ' IN (?)' => $idsBunch
                    ]
                );
            }
            foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                $this->attributeResource->getConnection()->delete(
                    $this->attributeResource->getTable($this->datetimeTable),
                    [
                        'attribute_id IN (?)' => [$this->getPriceFromAttributeId(), $this->getPriceToAttributeId()],
                        $this->getEntityLinkField() . ' IN (?)' => $idsBunch
                    ]
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete Prices'),
                $e
            );
        }

        return true;
    }

    /**
     * Get link field.
     *
     * @return string
     * @since 2.2.0
     */
    public function getEntityLinkField()
    {
        return $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
    }

    /**
     * Update items in database.
     *
     * @param array $items
     * @param string $table
     * @return void
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @return array
     * @since 2.2.0
     */
    private function retrieveAffectedIds(array $skus)
    {
        $affectedIds = [];

        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $productIds) {
            $affectedIds = array_merge($affectedIds, array_keys($productIds));
        }

        return array_unique($affectedIds);
    }
}
