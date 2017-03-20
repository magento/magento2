<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

/**
 * Persists tier prices.
 */
class TierPricePersistence
{
    /**
     * Number or items per each operation.
     *
     * @var int
     */
    private $itemsPerOperation = 500;

    /**
     * Tier price resource model.
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    private $tierpriceResource;

    /**
     * Metadata pool.
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * TierPricePersister constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierpriceResource
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierpriceResource,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->tierpriceResource = $tierpriceResource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Get tier prices by product IDs.
     *
     * @param array $ids
     * @return array
     */
    public function get(array $ids)
    {
        $select = $this->tierpriceResource->getConnection()->select()->from($this->tierpriceResource->getMainTable());
        return $this->tierpriceResource->getConnection()->fetchAll(
            $select->where($this->getEntityLinkField() . ' IN (?)', $ids)
        );
    }

    /**
     * Update tier prices.
     *
     * @param array $prices
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function update(array $prices)
    {
        $connection = $this->tierpriceResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($prices, $this->itemsPerOperation) as $pricesBunch) {
                $this->tierpriceResource->getConnection()->insertOnDuplicate(
                    $this->tierpriceResource->getMainTable(),
                    $pricesBunch,
                    ['value', 'percentage_value']
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save Tier Prices'),
                $e
            );
        }
    }

    /**
     * Replace prices.
     *
     * @param array $prices
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function replace(array $prices, array $ids)
    {
        $connection = $this->tierpriceResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                $this->tierpriceResource->getConnection()->delete(
                    $this->tierpriceResource->getMainTable(),
                    [$this->getEntityLinkField() . ' IN (?)' => $idsBunch]
                );
            }

            foreach (array_chunk($prices, $this->itemsPerOperation) as $pricesBunch) {
                $this->tierpriceResource->getConnection()->insertMultiple(
                    $this->tierpriceResource->getMainTable(),
                    $pricesBunch
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not replace Tier Prices'),
                $e
            );
        }
    }

    /**
     * Delete tier prices by IDs.
     *
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(array $ids)
    {
        $connection = $this->tierpriceResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                $this->tierpriceResource->getConnection()->delete(
                    $this->tierpriceResource->getMainTable(),
                    ['value_id IN (?)' => $idsBunch]
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete Tier Prices'),
                $e
            );
        }
    }

    /**
     * Get link field.
     *
     * @return string
     */
    public function getEntityLinkField()
    {
        return $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
    }
}
